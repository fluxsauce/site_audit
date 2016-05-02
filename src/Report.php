<?php
/**
 * @file
 * Contains Drupal\site_audit\Report.
 */

namespace Drupal\site_audit;

use Drupal\Console\Style\DrupalStyle;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Report.
 */
abstract class Report {
  use StringTranslationTrait;

  /**
   * Individual check objects.
   *
   * @var array
   */
  protected $checks;

  /**
   * Percentage pass.
   *
   * @var int
   */
  protected $percent;

  /**
   * Maximum score.
   *
   * @var int
   */
  protected $scoreMax = 0;

  /**
   * Total score.
   *
   * @var int
   */
  protected $scoreTotal = 0;

  /**
   * Flag to indicate whether any of the checks are a complete FAIL.
   *
   * @var boolean
   */
  protected $hasFail = FALSE;

  /**
   * Container that's passed between each Check.
   *
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
    $name_exploded = explode('\\', get_class($this));
    array_pop($name_exploded);
    return substr(get_class($this), strlen(implode('\\', $name_exploded)) + 1);
  }

  /**
   * Constructor; loads and executes checks based on the name of this report.
   */
  public function __construct() {
    $base_class_name = 'Drupal\site_audit\Checks\\' . $this->getReportName() . '\\';
    $percent_override = NULL;

    $checks_to_skip = array();

    $checks_to_perform = $this->getCheckNames();

    foreach ($checks_to_perform as $key => $check_name) {
      if (in_array($this->getReportName() . $check_name, $checks_to_skip)) {
        unset($checks_to_perform[$key]);
      }
    }

    if (empty($checks_to_perform)) {
      throw new \RuntimeException($this->t('No checks are available!'));
    }

    $config = \Drupal::config('site_audit');
    foreach ($checks_to_perform as $check_name) {
      $class_name = $base_class_name . $check_name;
      $opt_out = $config->get('opt_out.' . $this->getReportName() . $check_name) != NULL;
      $check = new $class_name($this->registry, $opt_out);

      // Calculate score.
      if ($check->getScore() != Check::AUDIT_CHECK_SCORE_INFO) {
        // Mark if there's a major failure.
        if ($check->getScore() == Check::AUDIT_CHECK_SCORE_FAIL) {
          $this->hasFail = TRUE;
        }
        // Total.
        $this->scoreTotal += $check->getScore();
        // Maximum.
        $this->scoreMax += Check::AUDIT_CHECK_SCORE_PASS;
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
        $this->percent = Check::AUDIT_CHECK_SCORE_INFO;
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
   * Render response as HTML; does not include head, body, etc.
   *
   * @return string
   *   Report as rendered HTML.
   */
  public function toHtml() {
    $ret_val = '<h2 id="' . get_class($this) . '">' . $this->getLabel();
    if ($this->percent != Check::AUDIT_CHECK_SCORE_INFO) {
      $ret_val .= ' <span class="label label-' . $this->getPercentCssClass() . '">' . $this->percent . '%</span>';
    }
    else {
      $ret_val .= ' <span class="label label-info">' . $this->t('Info') . '</span>';
    }
    $ret_val .= '</h2>';
    if ($this->percent == 100) {
      $ret_val .= '<p class="text-success">';
      $ret_val .= '<strong>' . $this->t('Well done!') . '</strong> ' . $this->t('No action required.');
      $ret_val .= '</p>';
    }
    if ($this->percent != 100) {
      foreach ($this->checks as $check) {
        if ($check->getScore() != Check::AUDIT_CHECK_SCORE_PASS || $this->percent == Check::AUDIT_CHECK_SCORE_INFO) {
          $ret_val .= '<div class="panel panel-' . $check->getScoreCssClass() . '">';
          // Heading.
          $ret_val .= '<div class="panel-heading"><strong>' . $check->getLabel() . '</strong>';
          if (TRUE) {
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
   * Render response through Drupal Console.
   */
  public function toConsole(ArgvInput $input, DrupalStyle $output) {
    if ($this->percent == Check::AUDIT_CHECK_SCORE_INFO) {
      $output->info($this->t('!label: Info', array(
        '!label' => $this->getLabel(),
      )));
    }
    else {
      $method = $this->getPercentSymphonyStyleMethod();
      $output->$method($this->t('!label: @percent%', array(
        '!label' => $this->getLabel(),
        '@percent' => $this->percent,
      )));
    }
    if ($this->percent == 100) {
      $output->block($this->t('No action required.'), 'OK', 'fg=black;bg=green', str_repeat(' ', 2));
    }
    $detail = $output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE;

    // Information or a problem.
    if ($detail || $this->percent != 100) {
      foreach ($this->checks as $check) {
        if ($detail || $check->getScore() != Check::AUDIT_CHECK_SCORE_PASS || $this->percent == Check::AUDIT_CHECK_SCORE_INFO) {
          // Heading.
          if ($detail) {
            $heading = $this->t('!label: !description', array(
              '!label' => $check->getLabel(),
              '!description' => $check->getDescription(),
            ));
          }
          else {
            $heading = $this->t('!label', array(
              '!label' => $check->getLabel(),
            ));
          }
          $output->block($heading, $check->getScoreSymfonyType(), $check->getScoreSymfonyStyle());

          // Result.
          $result = $check->getResult();
          if (is_array($result)) {
            $output->table($result['headers'], $result['rows']);
          }
          else {
            $output->simple($result);
          }

          // Action.
          $action = $check->renderAction();
          if ($action) {
            $output->info(str_repeat(' ', 2) . $this->t('!action', array(
              '!action' => $check->renderAction(),
            )));
          }
        }
      }
    }
  }

  /**
   * Get the calculated percentage.
   *
   * @return int
   *   Calculated percentage.
   */
  public function getPercent() {
    return $this->percent;
  }

  /**
   * Get the CSS class associated with a percentage.
   *
   * @return string
   *   Twitter Bootstrap CSS class.
   */
  public function getPercentCssClass() {
    if ($this->percent > 80) {
      return 'success';
    }
    if ($this->percent > 65) {
      return 'error';
    }
    if ($this->percent >= 0) {
      return 'caution';
    }
    return 'info';
  }

  /**
   * Get the SymfonyStyle method assiociated with a percentage.
   *
   * @return string
   *   Symfony\Component\Console\Style method.
   */
  public function getPercentSymphonyStyleMethod() {
    if ($this->percent > 80) {
      return 'success';
    }
    if ($this->percent > 65) {
      return 'warning';
    }
    if ($this->percent >= 0) {
      return 'error';
    }
    return 'note';
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
   * @return array
   *   Machine readable names.
   */
  abstract public function getCheckNames();

}
