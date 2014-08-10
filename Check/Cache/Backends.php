<?php
/**
 * @file
 * Contains \SiteAudit\Check\Cache\Backends.
 */

class SiteAuditCheckCacheBackends extends SiteAuditCheckAbstract {
  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Caching backends');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Detail caching backends.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {
    return dt('Syntax error in configuration!');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultInfo().
   */
  public function getResultInfo() {
    return dt('Using the database as a caching backend, which is less efficient than a dedicated key-value store.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultPass().
   */
  public function getResultPass() {
    return dt('The following caching backends are being used: @backends', array(
      '@backends' => implode(', ', $this->registry['cache_backends']),
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
        return dt('Consider using a caching backend such as redis.');
      }
      elseif (drush_get_option('vendor') == 'acquia') {
        return dt('Consider using a caching backend such as memcache.');
      }
      return dt('Consider using a caching backend such as redis or memcache.');
    }
    if ($this->score == SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL) {
      return dt('$conf["cache_backends"] should be an array.');
    }
  }

  /**
   * Implements \SiteAudit\Check\Abstract\calculateScore().
   */
  public function calculateScore() {
    $this->registry['cache_backends'] = variable_get('cache_backends', array());
    if (is_string($this->registry['cache_backends'])) {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL;
    }
    if (is_array($this->registry['cache_backends']) && !empty($this->registry['cache_backends'])) {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS;
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO;
  }
}
