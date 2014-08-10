<?php
/**
 * @file
 * Contains \SiteAudit\Check\Cache\Lock.
 */

class SiteAuditCheckCacheLock extends SiteAuditCheckAbstract {
  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Lock API');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Determine the default locking mechanism.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getResultInfo().
   */
  public function getResultInfo() {
    return dt('Using the default semaphore database table.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultPass().
   */
  public function getResultPass() {
    return dt('Using @lock_inc', array(
      '@lock_inc' => $this->registry['lock_inc'],
    ));
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultWarn().
   */
  public function getResultWarn() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getAction().
   */
  public function getAction() {
    if ($this->score == SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO) {
      if (drush_get_option('vendor') == 'pantheon') {
        return dt('Consider using a dedicated API to a caching backend such as redis.');
      }
      elseif (drush_get_option('vendor') == 'acquia') {
        return dt('Consider using a dedicated API to a caching backend such as memcache.');
      }
      return dt('Consider using a dedicated API to a caching backend, such as redis or memcache.');
    }
  }

  /**
   * Implements \SiteAudit\Check\Abstract\calculateScore().
   */
  public function calculateScore() {
    $this->registry['lock_inc'] = variable_get('lock_inc');
    if (!$this->registry['lock_inc']) {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO;
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS;
  }
}
