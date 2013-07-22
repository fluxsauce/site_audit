<?php
/**
 * @file
 * Contains \AuditCheckCacheAnon.
 */

class AuditCheckCacheAnon extends AuditCheck {
  /**
   * Implements \AuditCheck\getLabel().
   */
  public function getLabel() {
    return dt('Anonymous caching');
  }

  /**
   * Implements \AuditCheck\getDescription().
   */
  public function getDescription() {
    return dt('Verify Drupal\'s anonymous page caching is enabled.');
  }

  /**
   * Implements \AuditCheck\getResultFail().
   */
  public function getResultFail() {
    return dt('Anonymous page caching is not enabled!');
  }

  /**
   * Implements \AuditCheck\getResultInfo().
   */
  public function getResultInfo() {}

  /**
   * Implements \AuditCheck\getResultPass().
   */
  public function getResultPass() {
    return dt('Anonymous caching is enabled.');
  }

  /**
   * Implements \AuditCheck\getResultWarning().
   */
  public function getResultWarning() {}

  /**
   * Implements \AuditCheck\getAction().
   */
  public function getAction() {
    if ($this->score == AuditCheck::AUDIT_CHECK_SCORE_FAIL) {
      return dt('Go to /admin/config/development/performance and check "Cache pages for anonymous users".');
    }
  }

  /**
   * Implements \AuditCheck\calculateScore().
   */
  public function calculateScore() {
    global $conf;
    if ($conf['cache']) {
      return AuditCheck::AUDIT_CHECK_SCORE_PASS;
    }
    return AuditCheck::AUDIT_CHECK_SCORE_FAIL;
  }
}
