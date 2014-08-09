<?php
/**
 * @file
 * Contains \SiteAudit\Check\Cache\PageExpire.
 */

class SiteAuditCheckCachePageExpire extends SiteAuditCheckAbstract {
  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Expiration of cached pages');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Verify that Drupal\'s cached pages last for at least 15 minutes.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {
    return dt('Expiration of cached pages not set!');
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
    global $conf;
    return dt('Expiration of cached pages is set to @minutes min.', array(
      '@minutes' => round($conf['page_cache_maximum_age'] / 60),
    ));
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultWarn().
   */
  public function getResultWarn() {
    global $conf;
    return dt('Expiration of cached pages only set to @minutes min.', array(
      '@minutes' => round($conf['page_cache_maximum_age'] / 60),
    ));
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getAction().
   */
  public function getAction() {
    if (!in_array($this->score, array(SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS))) {
      return dt('Go to /admin/config/development/performance and set "Expiration of cached pages" to 15 min or above.');
    }
  }

  /**
   * Implements \SiteAudit\Check\Abstract\calculateScore().
   */
  public function calculateScore() {
    global $conf;
    if (!isset($conf['page_cache_maximum_age']) || !$conf['page_cache_maximum_age']) {
      if (site_audit_env_is_dev()) {
        return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO;
      }
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL;
    }
    elseif ($conf['page_cache_maximum_age'] >= 900) {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS;
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN;
  }
}
