<?php
/**
 * @file
 * Contains \SiteAudit\Check\Cache\PageCompression.
 */

class SiteAuditCheckCachePageCompression extends SiteAuditCheckAbstract {
  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Cached page compression');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    if (drush_get_option('vendor') == 'pantheon') {
      return dt('Verify that Drupal is not set to compress cached pages.');
    }
    else {
      return dt('Verify that Drupal is set to compress cached pages.');
    }
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {
    if (drush_get_option('vendor') == 'pantheon') {
      return dt('Cached pages are compressed!');
    }
    else {
      return dt('Cached pages are not compressed!');
    }
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultInfo().
   */
  public function getResultInfo() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getResultPass().
   */
  public function getResultPass() {
    if (drush_get_option('vendor') == 'pantheon') {
      return dt('Cached pages are not compressed.');
    }
    else {
      return dt('Cached pages are compressed.');
    }
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
      if (drush_get_option('vendor') == 'pantheon') {
        return dt('Pantheon compresses your pages for you. Don\'t make Drupal do the work! Go to /admin/config/development/performance and uncheck "Compress cached pages".');
      }
      else {
        return dt('Go to /admin/config/development/performance and check "Compress cached pages".');
      }
    }
  }

  /**
   * Implements \SiteAudit\Check\Abstract\calculateScore().
   */
  public function calculateScore() {
    global $conf;
    if (drush_get_option('vendor') == 'pantheon') {
      if (!$conf['page_compression']) {
        return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS;
      }
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL;
    }
    if (!$conf['page_compression']) {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL;
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS;
  }
}
