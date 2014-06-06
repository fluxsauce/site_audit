<?php
/**
 * @file
 * Contains \SiteAudit\Check\Cache\DefaultClass.
 */

class SiteAuditCheckCacheDefaultClass extends SiteAuditCheckAbstract {
  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Default class');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Determine the default cache class, used whenever no alternative is specified.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getResultInfo().
   */
  public function getResultInfo() {
    return dt('Using @cache_default_class.', array(
      '@cache_default_class' => $this->registry['cache_default_class'],
    ));
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultPass().
   */
  public function getResultPass() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getResultWarn().
   */
  public function getResultWarn() {
    return dt('Using DrupalDatabaseCache as the default of all caches, but alternate caching backends are available. Specify $conf["cache_default_class"] to improve performance.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getAction().
   */
  public function getAction() {}

  /**
   * Implements \SiteAudit\Check\Abstract\calculateScore().
   */
  public function calculateScore() {
    $this->registry['cache_default_class'] = variable_get('cache_default_class', 'DrupalDatabaseCache');
    if ($this->registry['cache_default_class'] == 'DrupalDatabaseCache' && is_array($this->registry['cache_backends']) && !empty($this->registry['cache_backends'])) {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN;
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO;
  }
}
