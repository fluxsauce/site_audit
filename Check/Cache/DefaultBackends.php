<?php
/**
 * @file
 * Contains \SiteAudit\Check\Cache\DefaultBackends.
 */

/**
 * Class SiteAuditCheckCacheDefaultBackends.
 */
class SiteAuditCheckCacheDefaultBackends extends SiteAuditCheckAbstract {

  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Default Caching backends');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Detail the default caching backends of all bins');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getResultInfo().
   */
  public function getResultInfo() {
    if (drush_get_option('html')) {
      $ret_val = '<table class="table table-condensed">';
      $ret_val .= '<thead><tr><th>Bin</th><th>Backend</th></tr></thead>';
      $ret_val .= '<tbody>';
      foreach ($this->registry['default_backends'] as $bin => $backend) {
        $ret_val .= "<tr><td>$bin</td><td>$backend</td></tr>";
      }
      $ret_val .= '</tbody>';
      $ret_val .= '</table>';
    }
    else {
      $ret_val  = 'Bin: Backend' . PHP_EOL;
      if (!drush_get_option('json')) {
        $ret_val .= str_repeat(' ', 4);
      }
      $ret_val .= '----------';
      foreach ($this->registry['default_backends'] as $bin => $backend) {
        $ret_val .= PHP_EOL;
        if (!drush_get_option('json')) {
          $ret_val .= str_repeat(' ', 4);
        }
        $ret_val .= "$bin: $backend";
      }
    }
    return $ret_val;
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultPass().
   */
  public function getResultPass() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getResultWarn().
   */
  public function getResultWarn() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getAction().
   */
  public function getAction() {}

  /**
   * Implements \SiteAudit\Check\Abstract\calculateScore().
   */
  public function calculateScore() {
    $container = \Drupal::getContainer();
    $defaults = $container->getParameter('cache_default_bin_backends');
    $this->registry['default_backends'] = array();
    foreach ($container->getParameter('cache_bins') as $bin) {
      if (isset($defaults[$bin])) {
        $this->registry['default_backends'][$bin] = $defaults[$bin];
      }
      else {
        $this->registry['default_backends'][$bin] = 'cache.backend.database';
      }
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO;
  }

}
