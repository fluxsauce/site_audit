<?php
/**
 * @file
 * Contains \SiteAudit\Report\Abstract.
 */

abstract class SiteAuditReportAbstract {
  /**
   * Individual check objects.
   * @var array
   */
  protected $checks;

  /**
   * Percentage pass.
   * @var int
   */
  protected $percent;

  /**
   * Maximum score.
   * @var int
   */
  protected $scoreMax = 0;

  /**
   * Total score.
   * @var int
   */
  protected $scoreTotal = 0;

  /**
   * Flag to indicate whether any of the checks are a complete FAIL.
   * @var boolean
   */
  protected $hasFail = FALSE;

  /**
   * Container that's passed between each SiteAuditCheckAbstract, better than a
   * global.
   * @var array
   */
  protected $registry = array();

  /**
   * Get the complete name of the report.
   *
   * @return string
   *   The report name.
   */
  protected function getReportName() {
    return substr(get_class($this), strlen('SiteAuditReport'));
  }

  /**
   * Constructor; loads and executes checks based on the name of this report.
   */
  public function __construct() {
    global $conf;

    $base_class_name = 'SiteAuditCheck' . $this->getReportName();
    $percent_override = NULL;

    $checks_to_skip = array();
    if (drush_get_option('skip')) {
      $checks_to_skip = explode(',', drush_get_option('skip'));
    }

    $checks_to_perform = $this->getCheckNames();

    foreach ($checks_to_perform as $key => $check_name) {
      if (in_array($this->getReportName() . $check_name, $checks_to_skip)) {
        unset($checks_to_perform[$key]);
      }
    }

    if (empty($checks_to_perform)) {
      // No message for audit_all.
      $command = drush_parse_command();
      if ($command['command'] == 'audit_all') {
        return FALSE;
      }
      return drush_set_error('SITE_AUDIT_NO_CHECKS', dt('No checks are available!'));
    }

    foreach ($checks_to_perform as $check_name) {
      $class_name = $base_class_name . $check_name;
      $check = new $class_name($this->registry, isset($conf['site_audit']['opt_out'][$this->getReportName() . $check_name]));

      // Calculate score.
      if ($check->getScore() != SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO) {
        // Mark if there's a major failure.
        if ($check->getScore() == SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL) {
          $this->hasFail = TRUE;
        }
        // Total.
        $this->scoreTotal += $check->getScore();
        // Maximum.
        $this->scoreMax += SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS;
      }
      // Allow Report percentage to be overridden.
      if ($check->getPercentOverride()) {
        $percent_override = $check->getPercentOverride();
      }
      // Combine registry.
      $this->registry = array_merge($this->registry, $check->getRegistry());
      // Store all checks.
      $this->checks[$class_name] = $check;
      // Abort the loop if the check says to bail.
      if ($check->shouldAbort()) {
        break;
      }
    }
    if ($percent_override) {
      $this->percent = $percent_override;
    }
    else {
      if ($this->scoreMax != 0) {
        $this->percent = round(($this->scoreTotal / $this->scoreMax) * 100);
      }
      else {
        $this->percent = SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO;
      }
    }
  }

  /**
   * Render response using JSON.
   *
   * @return string
   *   Format report as JSON.
   */
  public function toJson() {
    $report = array(
      'percent' => $this->percent,
      'label' => $this->getLabel(),
      'checks' => array(),
    );
    foreach ($this->checks as $check) {
      $report['checks'][get_class($check)] = array(
        'label' => $check->getLabel(),
        'description' => $check->getDescription(),
        'result' => $check->getResult(),
        'action' => $check->renderAction(),
        'score' => $check->getScore(),
      );
    }
    return json_encode($report);
  }

  /**
   * Render response using Drush.
   *
   * @return string
   *   Report using Drush native output functions.
   */
  public function toDrush() {
    if ($this->percent == SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO) {
      drush_print(dt('!label: Info', array(
        '!label' => $this->getLabel(),
      )));
    }
    else {
      drush_print(dt('!label: @percent%', array(
        '!label' => $this->getLabel(),
        '@percent' => $this->percent,
      )));
    }
    if ($this->percent == 100) {
      if (drush_get_option('gist')) {
        drush_print(str_repeat(' ', 2) . dt('No action required.'));
      }
      else {
        drush_log(str_repeat(' ', 2) . dt('No action required.'), 'success');
      }
    }
    if (drush_get_option('detail') || $this->percent != 100) {
      foreach ($this->checks as $check) {
        if (drush_get_option('detail') || $check->getScore() != SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS || $this->percent == SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO) {
          if (drush_get_option('detail')) {
            drush_print(str_repeat(' ', 2) . dt('!label: !description', array(
              '!label' => $check->getLabel(),
              '!description' => $check->getDescription(),
            )));
          }
          else {
            if ($check->getScore() != SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO) {
              drush_print(str_repeat(' ', 2) . dt('!label', array(
                '!label' => $check->getLabel(),
              )));
            }
          }
          if ($this->percent == SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO || drush_get_option('detail')) {
            if (($check->getScore() != SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO) || drush_get_option('detail')) {
              drush_print(str_repeat(' ', 4) . dt('!result', array(
                '!result' => $check->getResult(),
              )));
            }
            else {
              drush_print(str_repeat(' ', 2) . dt('!result', array(
                '!result' => $check->getResult(),
              )));
            }
          }
          else {
            if (drush_get_option('gist')) {
              drush_log(str_repeat(' ', 4) . dt('!result', array(
                '!result' => $check->getResult(),
              )));
            }
            else {
              drush_log(str_repeat(' ', 4) . dt('!result', array(
                '!result' => $check->getResult(),
              )), $check->getScoreDrushLevel());
            }
          }
          if ($check->renderAction()) {
            drush_print(str_repeat(' ', 6) . dt('!action', array(
              '!action' => $check->renderAction(),
            )));
          }
        }
      }
    }
  }

  /**
   * Render response as HTML; does not include head, body, etc.
   *
   * @return string
   *   Report as rendered HTML.
   */
  public function toHtml() {
    $ret_val = '<h2 id="' . get_class($this) . '">' . $this->getLabel();
    if ($this->percent != SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO) {
      $ret_val .= ' <span class="label label-' . $this->getPercentCssClass() . '">' . $this->percent . '%</span>';
    }
    else {
      $ret_val .= ' <span class="label label-info">' . dt('Info') . '</span>';
    }
    $ret_val .= '</h2>';
    if ($this->percent == 100) {
      $ret_val .= '<p class="text-success">';
      $ret_val .= '<strong>' . dt('Well done!') . '</strong> ' . dt('No action required.');
      $ret_val .= '</p>';
    }
    if (drush_get_option('detail') || $this->percent != 100) {
      foreach ($this->checks as $check) {
        if (drush_get_option('detail') || $check->getScore() != SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS || $this->percent == SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO) {
          $ret_val .= '<div class="panel panel-' . $check->getScoreCssClass() . '">';
          // Heading.
          $ret_val .= '<div class="panel-heading"><strong>' . $check->getLabel() . '</strong>';
          if (drush_get_option('detail')) {
            $ret_val .= '<small> - ' . $check->getDescription() . '</small>';
          }
          $ret_val .= '</div>';
          // Result.
          $ret_val .= '<p>' . $check->getResult() . '</p>';
          if ($check->renderAction()) {
            $ret_val .= '<div class="well well-small">' . $check->renderAction() . '</div>';
          }
          $ret_val .= '</div>';
        }
      }
    }
    $ret_val .= "\n";
    return $ret_val;
  }

  /**
   * Render the report; respects drush options.
   */
  public function render() {
    if (empty($this->checks)) {
      return;
    }
    if (drush_get_option('html')) {
      $command = drush_get_command();
      if (drush_get_option('bootstrap') && ($command['command'] != 'audit_all')) {
        echo file_get_contents(SITE_AUDIT_BASE_PATH . '/html/header.html');
      }
      echo $this->toHtml();
      if (drush_get_option('bootstrap') && ($command['command'] != 'audit_all')) {
        echo file_get_contents(SITE_AUDIT_BASE_PATH . '/html/footer.html');
      }
    }
    elseif (drush_get_option('json')) {
      echo $this->toJson();
    }
    else {
      $this->toDrush();
    }
  }

  /**
   * Get the calculated percentage.
   * @return int
   *   Calculated percentage.
   */
  public function getPercent() {
    return $this->percent;
  }

  /**
   * Get the CSS class associated with a percentage.
   * @return string
   *   Twitter Bootstrap CSS class.
   */
  public function getPercentCssClass() {
    if ($this->percent > 80) {
      return 'success';
    }
    if ($this->percent > 65) {
      return 'warning';
    }
    if ($this->percent >= 0) {
      return 'danger';
    }
    return 'info';
  }

  /**
   * Get the label for the report of what is being checked.
   *
   * @return string
   *   Human readable label.
   */
  abstract public function getLabel();

  /**
   * Get the names of all the checks within the report.
   *
   * Uses the key 'checks' within the command to populate. Order matters, so
   * if you implement hook_drush_command_alter(), try to add checks in a logical
   * order, IE don't check for something specific about Views if Views is
   * disabled.
   *
   * @return array
   *   Machine readable names.
   */
  public function getCheckNames() {
    $commands = drush_get_commands();

    // Guess the name of the Drush command.
    $command_name_pieces = preg_split('/(?=[A-Z])/', get_called_class());
    unset($command_name_pieces[0], $command_name_pieces[1], $command_name_pieces[3]);
    $command_name = strtolower(implode('_', $command_name_pieces));
    $command = $commands[$command_name];

    drush_command_invoke_all_ref('drush_command_alter', $command);

    $checks = array();
    foreach ($command['checks'] as $check) {
      if (is_array($check)) {
        $checks[] = $check['name'];
        require_once $check['location'];
      }
      else {
        $checks[] = $check;
        $base_class_name = 'SiteAuditCheck' . $this->getReportName();
        $class_name = $base_class_name . $check;
        if (!class_exists($class_name)) {
          require_once SITE_AUDIT_BASE_PATH . "/Check/{$this->getReportName()}/$check.php";
        }
      }
    }

    return $checks;
  }
}
