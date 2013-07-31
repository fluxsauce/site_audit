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
   * Constructor; loads and executes checks based on the name of this report.
   */
  public function __construct() {
    $report_name = substr(get_class($this), strlen('SiteAuditCheck') + 1);
    $base_class_name = 'SiteAuditCheck' . $report_name;
    require_once __DIR__ . '/../Check/Abstract.php';
    $percent_override = NULL;
    foreach ($this->getCheckNames() as $check_name) {
      require_once __DIR__ . "/../Check/$report_name/$check_name.php";
      $class_name = $base_class_name . $check_name;
      $check = new $class_name($this->registry);
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
        drush_print(str_pad(dt('No action required.'), 2, ' ', STR_PAD_LEFT));
      }
      else {
        drush_log(str_pad(dt('No action required.'), 'success'), 2, ' ', STR_PAD_LEFT);
      }
    }
    if (drush_get_option('detail') || $this->percent != 100) {
      foreach ($this->checks as $check) {
        if (drush_get_option('detail') || $check->getScore() != SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS || $this->percent == SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO) {
          if (drush_get_option('detail')) {
            drush_print(str_pad(dt('!label: !description', array(
              '!label' => $check->getLabel(),
              '!description' => $check->getDescription(),
            )), 2, ' ', STR_PAD_LEFT));
          }
          else {
            if ($check->getScore() != SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO) {
              drush_print(str_pad(dt('!label', array(
                '!label' => $check->getLabel(),
              )), 2, ' ', STR_PAD_LEFT));
            }
          }
          if ($this->percent == SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO || drush_get_option('detail')) {
            if ($check->getScore() != SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO) {
              drush_print(str_pad(dt('!result', array(
                '!result' => $check->getResult(),
              )), 4, ' ', STR_PAD_LEFT));
            }
            else {
              drush_print(str_pad(dt('!result', array(
                '!result' => $check->getResult(),
              )), 2, ' ', STR_PAD_LEFT));
            }
          }
          else {
            if (drush_get_option('gist')) {
              drush_log(str_pad(dt('!result', array(
                '!result' => $check->getResult(),
              )), 4, ' ', STR_PAD_LEFT));
            }
            else {
              drush_log(str_pad(dt('!result', array(
                '!result' => $check->getResult(),
              )), 4, ' ', STR_PAD_LEFT), $check->getScoreDrushLevel());
            }
          }
          if ($check->getAction()) {
            drush_print(str_pad(dt('!action', array(
              '!action' => $check->getAction(),
            )), 6, ' ', STR_PAD_LEFT));
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
      $ret_val .= ' <span class="label label-'. $this->getPercentCssClass() . '">' . $this->percent . '%</span>';
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
          if ($check->getAction()) {
            $ret_val .= '<div class="well well-small">' . $check->getAction() . '</div>';
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
    if (drush_get_option('html')) {
      echo $this->toHtml();
    }
    else {
      $this->toDrush();
    }
  }

  /**
   * Get the calculated percentage.
   * @return int
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
   * Abstract instead of using pattern matching so order can be manually
   * specified.
   *
   * @return array
   *   Machine readable names.
   */
  abstract public function getCheckNames();
}
