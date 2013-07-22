<?php
/**
 * @file
 * Contains \AuditCheck.
 */

abstract class AuditCheck {
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
   * Label to describe, high level what the check is doing.
   * @var string
   */
  public $label;

  /**
   * More verbose description of what is being checked.
   * @var string
   */
  public $description;

  /**
   * What should be done, if anything. Only shown if check did not pass.
   * @var string
   */
  public $action;

  /**
   * Quantifiable number associated with result on a scale of 0 to 2.
   * @var int
   */
  public $score;

  /**
   * Human readable message associated with the result of the check.
   * @var string
   */
  public $result;

  /**
   * Human readable message associated with the result of the check.
   * @var string
   */
  public $info;

  /**
   * Indicate that no other checks should be run after this check.
   * @var boolean
   */
  public $abort = FALSE;

  /**
   * Indicates that HTML will be returned, don't escape.
   * @var boolean
   */
  public $html = FALSE;

  /**
   * Use for passing data between checks within a report.
   * @var array
   */
  public $registry;

  /**
   * Constructor.
   *
   * @param array $registry
   *   Aggregates data from each individual check.
   */
  public function __construct($registry) {
    $this->registry = $registry;
    $this->label = $this->getLabel();
    $this->description = $this->getDescription();
    $this->score = $this->getScore();
    $this->result = $this->getResult();
    $this->action = $this->getAction();
    if (drush_get_option('html')) {
      $this->html = TRUE;
    }
  }

  /**
   * Determine the result message based on the score.
   *
   * @return string
   *   Human readable message for a given status.
   */
  public function getResult() {
    switch ($this->score) {
      case AuditCheck::AUDIT_CHECK_SCORE_PASS:
        return $this->getResultPass();

      case AuditCheck::AUDIT_CHECK_SCORE_WARN:
        return $this->getResultWarning();

      case AuditCheck::AUDIT_CHECK_SCORE_INFO:
        return $this->getResultInfo();

      default:
        return $this->getResultFail();

    }
  }

  /**
   * Get a human readable label for a score.
   *
   * @return string
   *   Pass, Recommended and so forth.
   */
  public function getScoreLabel() {
    switch ($this->score) {
      case AuditCheck::AUDIT_CHECK_SCORE_PASS:
        return dt('Pass');

      case AuditCheck::AUDIT_CHECK_SCORE_WARN:
        return dt('Recommended');

      case AuditCheck::AUDIT_CHECK_SCORE_INFO:
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
      case AuditCheck::AUDIT_CHECK_SCORE_PASS:
        return AuditCheck::AUDIT_CHECK_COLOR_PASS;

      case AuditCheck::AUDIT_CHECK_SCORE_WARN:
        return AuditCheck::AUDIT_CHECK_COLOR_WARN;

      case AuditCheck::AUDIT_CHECK_SCORE_INFO:
        return AuditCheck::AUDIT_CHECK_COLOR_INFO;

      default:
        return AuditCheck::AUDIT_CHECK_COLOR_FAIL;

    }
  }

  /**
   * Get the Drush message level associated with a score.
   * @return string
   *   Converts the score (integer) to Drush levels.
   */
  public function getScoreDrushLevel() {
    switch ($this->score) {
      case AuditCheck::AUDIT_CHECK_SCORE_PASS:
        return AuditCheck::AUDIT_CHECK_DRUSH_PASS;

      case AuditCheck::AUDIT_CHECK_SCORE_WARN:
        return AuditCheck::AUDIT_CHECK_DRUSH_WARN;

      case AuditCheck::AUDIT_CHECK_SCORE_INFO:
        return AuditCheck::AUDIT_CHECK_DRUSH_INFO;

      default:
        return AuditCheck::AUDIT_CHECK_DRUSH_FAIL;

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
   *   A sentence describing the check; shown in verbose mode.
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
  abstract public function getResultWarning();

  /**
   * Get action items for a user to perform if the check did not pass.
   * @return string
   *   Get a description of what happened in a warning check.
   */
  abstract public function getAction();

  /**
   * Get a quantifiable number for check.
   * @return int
   *   Constants indicating pass, fail and so forth.
   */
  abstract public function getScore();
}
