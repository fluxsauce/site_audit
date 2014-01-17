<?php
/**
 * @file
 * Contains \SiteAudit\Check\Block\Cache.
 */

class SiteAuditCheckBlockCache extends SiteAuditCheckAbstract {
  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Caching');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Verify Drupal\'s block caching is enabled.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {
    return dt('Block caching is not enabled!');
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
    return dt('Block caching is enabled.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultWarn().
   */
  public function getResultWarn() {
    return dt('Block caching is inactive because you have enabled modules defining content access restrictions.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getAction().
   */
  public function getAction() {
    if ($this->score == SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL) {
      return dt('Go to /admin/config/development/performance and check "Cache blocks".');
    }
  }

  /**
   * Implements \SiteAudit\Check\Abstract\calculateScore().
   */
  public function calculateScore() {
    global $conf;
    if ($conf['block_cache']) {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS;
    }
    // Same as block_form_system_performance_settings_alter().
    elseif (count(module_implements('node_grants'))) {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN;
    }
    // Block caching is off.
    if (site_audit_env_is_dev()) {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO;
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL;
  }
}
