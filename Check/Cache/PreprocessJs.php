<?php
/**
 * @file
 * Contains \SiteAudit\Check\Cache\PreprocessJs.
 */

class SiteAuditCheckCachePreprocessJs extends SiteAuditCheckAbstract {
  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Aggregate JavaScript files in Drupal');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Verify that Drupal is aggregating JavaScript.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {
    return dt('JavaScript aggregation is not enabled!');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultInfo().
   */
  public function getResultInfo() {
    return $this->getResultFail();
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultPass().
   */
  public function getResultPass() {
    return dt('JavaScript aggregation is enabled.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultWarn().
   */
  public function getResultWarn() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getAction().
   */
  public function getAction() {
    if (!in_array($this->score, array(SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS))) {
      return dt('Go to /admin/config/development/performance and check "Aggregate JavaScript files".');
    }
  }

  /**
   * Implements \SiteAudit\Check\Abstract\calculateScore().
   */
  public function calculateScore() {
    global $conf;
    if ($conf['preprocess_js']) {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS;
    }
    if (site_audit_env_is_dev()) {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO;
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL;
  }
}
