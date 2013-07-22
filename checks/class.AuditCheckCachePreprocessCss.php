<?php
/**
 * @file
 * Contains \AuditCheckCachePreprocessCss.
 */

class AuditCheckCachePreprocessCss extends AuditCheck {
  /**
   * Implements \AuditCheck\getLabel().
   */
  public function getLabel() {
    return dt('Aggregate and compress CSS files in Drupal');
  }

  /**
   * Implements \AuditCheck\getDescription().
   */
  public function getDescription() {
    return dt('Verify that Drupal is aggregating and compressing CSS.');
  }

  /**
   * Implements \AuditCheck\getResultFail().
   */
  public function getResultFail() {
    return dt('CSS aggregation and compression is not enabled!');
  }

  /**
   * Implements \AuditCheck\getResultInfo().
   */
  public function getResultInfo() {}

  /**
   * Implements \AuditCheck\getResultPass().
   */
  public function getResultPass() {
    return dt('CSS aggregation and compression is enabled.');
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
      return dt('Go to /admin/config/development/performance and check "Aggregate and compress CSS files".');
    }
  }

  /**
   * Implements \AuditCheck\calculateScore().
   */
  public function calculateScore() {
    global $conf;
    if ($conf['preprocess_css']) {
      return AuditCheck::AUDIT_CHECK_SCORE_PASS;
    }
    return AuditCheck::AUDIT_CHECK_SCORE_FAIL;
  }
}
