<?php
/**
 * @file
 * Contains \AuditCheckWatchdogEnabled.
 */

class AuditCheckWatchdogEnabled extends AuditCheck {
  /**
   * Implements \AuditCheck\getLabel().
   */
  public function getLabel() {
    return dt('dblog status');
  }

  /**
   * Implements \AuditCheck\getDescription().
   */
  public function getDescription() {
    return dt('Check to see if database logging is enabled');
  }

  /**
   * Implements \AuditCheck\getResultFail().
   */
  public function getResultFail() {}

  /**
   * Implements \AuditCheck\getResultInfo().
   */
  public function getResultInfo() {
    return dt('Database logging (dblog) is not enabled; if the site is having problems, consider enabling it for debugging.');
  }

  /**
   * Implements \AuditCheck\getResultPass().
   */
  public function getResultPass() {
    return dt('Database logging (dblog) is enabled.');
  }

  /**
   * Implements \AuditCheck\getResultWarning().
   */
  public function getResultWarning() {}

  /**
   * Implements \AuditCheck\getAction().
   */
  public function getAction() {}

  /**
   * Implements \AuditCheck\calculateScore().
   */
  public function calculateScore() {
    if (!module_exists('dblog')) {
      $this->abort = TRUE;
      return AuditCheck::AUDIT_CHECK_SCORE_INFO;
    }
    return AuditCheck::AUDIT_CHECK_SCORE_PASS;
  }
}
