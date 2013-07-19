<?php

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

  public $registry;

  /**
   * Constructor.
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
   * @param int $score
   * @return string
   */
  public function getResult() {
    switch ($this->score) {
      case AuditCheck::AUDIT_CHECK_SCORE_PASS:{
        return $this->getResultPass();
        break;
      }
      case AuditCheck::AUDIT_CHECK_SCORE_WARN:{
        return $this->getResultWarning();
        break;
      }
      case AuditCheck::AUDIT_CHECK_SCORE_INFO:{
        return $this->getResultInfo();
        break;
      }
      default:{
        return $this->getResultFail();
        break;
      }
    }
  }

  /**
   * Get a human readable label for a score.
   * @return string
   */
  public function getScoreLabel() {
    switch ($this->score) {
      case AuditCheck::AUDIT_CHECK_SCORE_PASS:{
        return dt('Pass');
        break;
      }
      case AuditCheck::AUDIT_CHECK_SCORE_WARN:{
        return dt('Recommended');
        break;
      }
      case AuditCheck::AUDIT_CHECK_SCORE_INFO:{
        return dt('Information');
        break;
      }
      default:{
        return dt('Blocking');
        break;
      }
    }
  }

  /**
   * Get the HTML color associated with a score.
   * @return string
   */
  public function getScoreColor() {
    switch ($this->score) {
      case AuditCheck::AUDIT_CHECK_SCORE_PASS:{
        return AuditCheck::AUDIT_CHECK_COLOR_PASS;
        break;
      }
      case AuditCheck::AUDIT_CHECK_SCORE_WARN:{
        return AuditCheck::AUDIT_CHECK_COLOR_WARN;
        break;
      }
      case AuditCheck::AUDIT_CHECK_SCORE_INFO:{
        return AuditCheck::AUDIT_CHECK_COLOR_INFO;
        break;
      }
      default:{
        return AuditCheck::AUDIT_CHECK_COLOR_FAIL;
        break;
      }
    }
  }

  /**
   * Get the Drush message level associated with a score.
   * @return string
   */
  public function getScoreDrushLevel() {
    switch ($this->score) {
      case AuditCheck::AUDIT_CHECK_SCORE_PASS:{
        return AuditCheck::AUDIT_CHECK_DRUSH_PASS;
        break;
      }
      case AuditCheck::AUDIT_CHECK_SCORE_WARN:{
        return AuditCheck::AUDIT_CHECK_DRUSH_WARN;
        break;
      }
      case AuditCheck::AUDIT_CHECK_SCORE_INFO:{
        return AuditCheck::AUDIT_CHECK_DRUSH_INFO;
        break;
      }
      default:{
        return AuditCheck::AUDIT_CHECK_DRUSH_FAIL;
        break;
      }
    }
  }

  /**
   * Get the label for the check that describes, high level what is happening.
   */
  abstract public function getLabel();

  /**
   * Get a more verbose description of what is being checked.
   */
  abstract public function getDescription();

  /**
   * Get a description of what happened in a failed check.
   */
  abstract public function getResultFail();

  /**
   * Get the result of a purely informational check.
   */
  abstract public function getResultInfo();

  /**
   * Get a description of what happened in a passed check.
   */
  abstract public function getResultPass();

  /**
   * Get a description of what happened in a warning check.
   */
  abstract public function getResultWarning();

  /**
   * Get a description of action items for a user to perform if the check
   * did not pass.
   */
  abstract public function getAction();

  /**
   * Get a quantifiable number for check.
   */
  abstract public function getScore();
}
