<?php

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
   * Get checks associated with a Report.
   *
   * @return array
   *   All Checks.
   */
  public function getChecks() {
    return $this->checks;
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
