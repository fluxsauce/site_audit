<?php
/**
 * @file
 * Contains \AuditCheckCachePreprocessJs.
 */

class AuditCheckCachePreprocessJs extends AuditCheck {
  /**
   * Implements \AuditCheck\getLabel().
   */
  public function getLabel() {
    return dt('Aggregate JavaScript files in Drupal');
  }

  /**
   * Implements \AuditCheck\getDescription().
   */
  public function getDescription() {
    return dt('Verify that Drupal is aggregating JavaScript.');
  }

  /**
   * Implements \AuditCheck\getResultFail().
   */
  public function getResultFail() {
    return dt('JavaScript aggregation is not enabled!');
  }

  /**
   * Implements \AuditCheck\getResultInfo().
   */
  public function getResultInfo() {}

  /**
   * Implements \AuditCheck\getResultPass().
   */
  public function getResultPass() {
    return dt('JavaScript aggregation is enabled.');
  }

  /**
   * Implements \AuditCheck\getResultWarning().
   */
  public function getResultWarning() {}

  /**
   * Implements \AuditCheck\getAction().
   */
  public function getAction() {
    if (!in_array($this->score, array(AuditCheck::AUDIT_CHECK_SCORE_PASS))) {
      return dt('Go to /admin/config/development/performance and check "Aggregate JavaScript files".');
    }
  }

  /**
   * Implements \AuditCheck\calculateScore().
   */
  public function calculateScore() {
    global $conf;
    if ($conf['preprocess_js']) {
      return AuditCheck::AUDIT_CHECK_SCORE_PASS;
    }
    return AuditCheck::AUDIT_CHECK_SCORE_FAIL;
  }
}
