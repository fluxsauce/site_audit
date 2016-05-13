<?php
/**
 * @file
 * Contains Drupal\site_audit\Check.
 */

namespace Drupal\site_audit;

use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Defines a Site Audit Check.
 */
abstract class Check {
  use StringTranslationTrait;

  const AUDIT_CHECK_SCORE_INFO = 3;
  const AUDIT_CHECK_SCORE_PASS = 2;
  const AUDIT_CHECK_SCORE_WARN = 1;
  const AUDIT_CHECK_SCORE_FAIL = 0;

  /**
   * Quantifiable number associated with result on a scale of 0 to 2.
   *
   * @var int
   */
  protected $score;

  /**
   * Names of checks that should not run as a result of this check.
   *
   * @var array
   */
  protected $abort = [];

  /**
   * User has opted out of this check in configuration.
   *
   * @var bool
   */
  protected $optOut = FALSE;

  /**
   * If set, will override the Report's percentage.
   *
   * @var int
   */
  protected $percentOverride;

  /**
   * Use for passing data between checks within a report.
   *
   * @var array
   */
  protected $registry;

  /**
   * Constructor.
   *
   * @param array $registry
   *   Aggregates data from each individual check.
   * @param bool $opt_out
   *   If set, will not perform checks.
   */
  public function __construct(array $registry, $opt_out = FALSE) {
    $this->registry = $registry;
    if ($opt_out) {
      $this->optOut = TRUE;
      $this->score = Check::AUDIT_CHECK_SCORE_INFO;
    }
  }

  /**
   * Determine the result message based on the score.
   *
   * @return string
   *   Human readable message for a given status.
   */
  public function getResult() {
    if ($this->optOut) {
      return dt('Opted-out in site configuration.');
    }
    switch ($this->score) {
      case Check::AUDIT_CHECK_SCORE_PASS:
        return $this->getResultPass();

      case Check::AUDIT_CHECK_SCORE_WARN:
        return $this->getResultWarn();

      case Check::AUDIT_CHECK_SCORE_INFO:
        return $this->getResultInfo();

      default:
        return $this->getResultFail();

    }
  }

  /**
   * Get a human readable label for a score.
   *
   * @return string
   *   Pass, Recommendation and so forth.
   */
  public function getScoreLabel() {
    switch ($this->score) {
      case Check::AUDIT_CHECK_SCORE_PASS:
        return dt('Pass');

      case Check::AUDIT_CHECK_SCORE_WARN:
        return dt('Warning');

      case Check::AUDIT_CHECK_SCORE_INFO:
        return dt('Information');

      default:
        return dt('Blocker');

    }
  }

  /**
   * Get the label for the check that describes, high level what is happening.
   *
   * @return string
   *   Get the label for the check that describes, high level what is happening.
   */
  abstract public function getLabel();

  /**
   * Get a more verbose description of what is being checked.
   *
   * @return string
   *   A sentence describing the check; shown in detail mode.
   */
  abstract public function getDescription();

  /**
   * Get the description of what happened in a failed check.
   *
   * @return string
   *   Something is explicitly wrong and requires action.
   */
  abstract public function getResultFail();

  /**
   * Get the result of a purely informational check.
   *
   * @return string
   *   Purely informational response.
   */
  abstract public function getResultInfo();

  /**
   * Get a description of what happened in a passed check.
   *
   * @return string
   *   Success; good job.
   */
  abstract public function getResultPass();

  /**
   * Get a description of what happened in a warning check.
   *
   * @return string
   *   Something is wrong, but not horribly so.
   */
  abstract public function getResultWarn();

  /**
   * Get action items for a user to perform if the check did not pass.
   *
   * @return string
   *   Actionable tasks to perform.
   */
  abstract public function getAction();

  /**
   * Display action items for a user to perform.
   *
   * @return string
   *   Actionable tasks to perform, or nothing if check is opted-out.
   */
  public function renderAction() {
    if ($this->optOut) {
      return '';
    }
    return $this->getAction();
  }

  /**
   * Calculate the score.
   *
   * @return int
   *   Constants indicating pass, fail and so forth.
   */
  abstract public function calculateScore();

  /**
   * Get a quantifiable number representing a check result; lazy initialization.
   *
   * @return int
   *   Constants indicating pass, fail and so forth.
   */
  public function getScore() {
    if (!isset($this->score)) {
      $this->score = $this->calculateScore();
    }
    return $this->score;
  }

  /**
   * Get the check registry.
   *
   * @return array
   *   Contains values calculated from this check and any prior checks.
   */
  public function getRegistry() {
    return $this->registry;
  }

  /**
   * Determine whether the check failed so badly that the report must stop.
   *
   * @return bool
   *   Whether to stop the abort after this check.
   */
  public function shouldAbort() {
    return $this->abort;
  }

  /**
   * Get the report percent override, if any.
   *
   * @return int
   *   The overridden percentage.
   */
  public function getPercentOverride() {
    return $this->percentOverride;
  }

}
