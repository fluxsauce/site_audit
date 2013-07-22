<?php
/**
 * @file
 * Contains \AuditCheckBestPracticesFast404.
 */

class AuditCheckBestPracticesFast404 extends AuditCheck {
  /**
   * Implements \AuditCheck\getLabel().
   */
  public function getLabel() {
    return dt('Fast 404 pages');
  }

  /**
   * Implements \AuditCheck\getDescription().
   */
  public function getDescription() {
    return dt('Check if enabled.');
  }

  /**
   * Implements \AuditCheck\getResultFail().
   */
  public function getResultFail() {}

  /**
   * Implements \AuditCheck\getResultInfo().
   */
  public function getResultInfo() {}

  /**
   * Implements \AuditCheck\getResultPass().
   */
  public function getResultPass() {
    return dt('Fast 404 pages are enabled.');
  }

  /**
   * Implements \AuditCheck\getResultWarning().
   */
  public function getResultWarning() {
    return dt('Fast 404 pages are not enabled.');
  }

  /**
   * Implements \AuditCheck\getAction().
   */
  public function getAction() {
    if ($this->score == AuditCheck::AUDIT_CHECK_SCORE_WARN) {
      return dt('See https://drupal.org/node/1313592 and default.settings.php for details on how to implement.');
    }
  }

  /**
   * Implements \AuditCheck\getScore().
   */
  public function getScore() {
    global $conf;
    if ($conf['404_fast_html']) {
      return AuditCheck::AUDIT_CHECK_SCORE_PASS;
    }
    return AuditCheck::AUDIT_CHECK_SCORE_WARN;
  }
}
