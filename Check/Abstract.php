<?php
/**
 * @file
 * Contains \SiteAudit\Check\Abstract.
 */

abstract class SiteAuditCheckAbstract {
  const AUDIT_CHECK_SCORE_PASS = 2;
  const AUDIT_CHECK_SCORE_WARN = 1;
  const AUDIT_CHECK_SCORE_FAIL = 0;
  const AUDIT_CHECK_SCORE_INFO = -1;

  const AUDIT_CHECK_COLOR_PASS = 'green';
  const AUDIT_CHECK_COLOR_WARN = '#d0cc35';
  const AUDIT_CHECK_COLOR_FAIL = 'red';
  const AUDIT_CHECK_COLOR_INFO = 'cyan';

  const AUDIT_CHECK_DRUSH_PASS = 'success';
  const AUDIT_CHECK_DRUSH_WARN = 'warning';
  const AUDIT_CHECK_DRUSH_FAIL = 'error';
  const AUDIT_CHECK_DRUSH_INFO = 'notice';

  /**
   * Quantifiable number associated with result on a scale of 0 to 2.
   * @var int
   */
  protected $score;

  /**
   * Indicate that no other checks should be run after this check.
   * @var boolean
   */
  protected $abort = FALSE;

  /**
   * If set, will override the Report's percentage.
   * @var int
   */
  protected $percent_override;

  /**
   * Use for passing data between checks within a report.
   * @var array
   */
  protected $registry;

  /**
   * Constructor.
   *
   * @param array $registry
   *   Aggregates data from each individual check.
   */
  public function __construct($registry) {
    $this->registry = $registry;
  }

  /**
   * Determine the result message based on the score.
   *
   * @return string
   *   Human readable message for a given status.
   */
  public function getResult() {
    switch ($this->score) {
      case SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS:
        return $this->getResultPass();

      case SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN:
        return $this->getResultWarn();

      case SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO:
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
      case SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS:
        return dt('Pass');

      case SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN:
        return dt('Recommendation');

      case SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO:
        return dt('Information');

      default:
        return dt('Blocking');

    }
  }

  /**
   * Get the HTML color associated with a score.
   * @return string
   *   Pretty colors. Will eventually be classes.
   */
  public function getScoreColor() {
    switch ($this->score) {
      case SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS:
        return SiteAuditCheckAbstract::AUDIT_CHECK_COLOR_PASS;

      case SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN:
        return SiteAuditCheckAbstract::AUDIT_CHECK_COLOR_WARN;

      case SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO:
        return SiteAuditCheckAbstract::AUDIT_CHECK_COLOR_INFO;

      default:
        return SiteAuditCheckAbstract::AUDIT_CHECK_COLOR_FAIL;

    }
  }

  /**
   * Get the Drush message level associated with a score.
   * @return string
   *   Converts the score (integer) to Drush levels.
   */
  public function getScoreDrushLevel() {
    switch ($this->score) {
      case SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS:
        return SiteAuditCheckAbstract::AUDIT_CHECK_DRUSH_PASS;

      case SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN:
        return SiteAuditCheckAbstract::AUDIT_CHECK_DRUSH_WARN;

      case SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO:
        return SiteAuditCheckAbstract::AUDIT_CHECK_DRUSH_INFO;

      default:
        return SiteAuditCheckAbstract::AUDIT_CHECK_DRUSH_FAIL;

    }
  }

  /**
   * Get the label for the check that describes, high level what is happening.
   * @return string
   *   Get the label for the check that describes, high level what is happening.
   */
  abstract public function getLabel();

  /**
   * Get a more verbose description of what is being checked.
   * @return string
   *   A sentence describing the check; shown in detail mode.
   */
  abstract public function getDescription();

  /**
   * Get the description of what happened in a failed check.
   * @return string
   *   Something is explicitly wrong and requires action.
   */
  abstract public function getResultFail();

  /**
   * Get the result of a purely informational check.
   * @return string
   *   Purely informational response.
   */
  abstract public function getResultInfo();

  /**
   * Get a description of what happened in a passed check.
   * @return string
   *   Success; good job.
   */
  abstract public function getResultPass();

  /**
   * Get a description of what happened in a warning check.
   * @return string
   *   Something is wrong, but not horribly so.
   */
  abstract public function getResultWarn();

  /**
   * Get action items for a user to perform if the check did not pass.
   * @return string
   *   Get a description of what happened in a warning check.
   */
  abstract public function getAction();

  /**
   * Calculate the score.
   * @return int
   *   Constants indicating pass, fail and so forth.
   */
  abstract public function calculateScore();

  /**
   * Get a quantifiable number representing a check result; lazy initialization.
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
   * @return int
   */
  public function getPercentOverride() {
    return $this->percent_override;
  }
}
