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
    $cache_settings = \Drupal::service('settings')->get('cache');
    $this->registry['cache_backends'] = [];
    foreach (array_keys($this->registry['cache_bins']) as $bin) {
      if (isset($cache_settings['bins']['cache.' . $bin])) {
        $this->registry['cache_backends'][] = $cache_settings['bins']['cache.' . $bin];
      }
      elseif (isset($cache_settings['default'])) {
        $this->registry['cache_backends'][] = $cache_settings['default'];
      }
      else {
        $this->registry['cache_backends'][] = 'cache.backend.database';
      }
    }
    $this->registry['cache_backends'] = array_unique($this->registry['cache_backends']);
    if (count($this->registry['cache_backends']) == 1 && $this->registry['cache_backends'][0] == 'cache.backend.database') {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO;
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS;
  }
}
