<?php
/**
 * @file
 * Contains \AuditCheckCacheLifetime.
 */

class AuditCheckCacheLifetime extends AuditCheck {
  /**
   * Implements \AuditCheck\getLabel().
   */
  public function getLabel() {
    return dt('Minimum cache lifetime');
  }

  /**
   * Implements \AuditCheck\getDescription().
   */
  public function getDescription() {
    return dt('Verify that Drupal\'s minimum cache lifetime is set to never expire.');
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
    return dt('Minimum cache lifetime is set to none.');
  }

  /**
   * Implements \AuditCheck\getResultWarning().
   */
  public function getResultWarning() {
    global $conf;
    return dt('Minimum cache lifetime is set to @minutes min.', array(
      '@minutes' => round($conf['cache_lifetime'] / 60),
    ));
  }

  /**
   * Implements \AuditCheck\getAction().
   */
  public function getAction() {
    if (!in_array($this->score, array(AuditCheck::AUDIT_CHECK_SCORE_PASS))) {
      return dt('Go to /admin/config/development/performance and set "Minimum cache lifetime" to none.');
    }
  }

  /**
   * Implements \AuditCheck\calculateScore().
   */
  public function calculateScore() {
    global $conf;
    if (!$conf['cache_lifetime']) {
      return AuditCheck::AUDIT_CHECK_SCORE_PASS;
    }
    return AuditCheck::AUDIT_CHECK_SCORE_WARN;
  }
}
