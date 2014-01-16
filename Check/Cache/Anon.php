<?php
/**
 * @file
 * Contains \SiteAudit\Check\Cache\Anon.
 */

class SiteAuditCheckCacheAnon extends SiteAuditCheckAbstract {
  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Anonymous caching');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Verify Drupal\'s anonymous page caching is enabled.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {
    return dt('Anonymous page caching is not enabled!');
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
    return dt('Anonymous caching is enabled.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultWarn().
   */
  public function getResultWarn() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getAction().
   */
  public function getAction() {
    if ($this->score == SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL) {
      return dt('Go to /admin/config/development/performance and check "Cache pages for anonymous users".');
    }
  }

  /**
   * Implements \SiteAudit\Check\Abstract\calculateScore().
   */
  public function calculateScore() {
    global $conf;
    if ($conf['cache']) {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS;
    }
    if (site_audit_env_is_dev()) {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO;
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL;
  }
}
