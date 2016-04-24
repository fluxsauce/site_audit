<?php
/**
 * @file
 * Contains \SiteAudit\Check\Cache\UsedBackends.
 */

/**
 * Class SiteAuditCheckCacheUsedBackends.
 */
class SiteAuditCheckCacheUsedBackends extends SiteAuditCheckAbstract {

  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Used Backends');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Detail cache backend used by each bin.');
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
      $ret_val .= '<thead><tr><th>Bin</th><th>Class</th></tr></thead>';
      $ret_val .= '<tbody>';
      foreach ($this->registry['used_backends'] as $bin => $class) {
        $ret_val .= "<tr><td>$bin</td><td>$class</td></tr>";
      }
      $ret_val .= '</tbody>';
      $ret_val .= '</table>';
    }
    else {
      $ret_val  = 'Bin: Class' . PHP_EOL;
      if (!drush_get_option('json')) {
        $ret_val .= str_repeat(' ', 4);
      }
      $ret_val .= '----------';
      foreach ($this->registry['used_backends'] as $bin => $class) {
        $ret_val .= PHP_EOL;
        if (!drush_get_option('json')) {
          $ret_val .= str_repeat(' ', 4);
        }
        $ret_val .= "$bin: $class";
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
  public function getResultWarn() {
    return dt('Using the database as a caching backend, which is less efficient than a dedicated key-value store.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getAction().
   */
  public function getAction() {
    if ($this->score == SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN) {
      return dt('Consider using a caching backend such as redis or memcache. For details on how to configure alternative caching backends, see https://api.drupal.org/api/drupal/core%21modules%21system%21core.api.php/group/cache/8#configuration .');
    }
  }

  /**
   * Implements \SiteAudit\Check\Abstract\calculateScore().
   */
  public function calculateScore() {
    $container = \Drupal::getContainer();
    foreach ($container->getParameter('cache_bins') as $bin) {
      $backend_class = get_class($container->get('cache.' . $bin)) . 'Factory';
      $backend = array_search($backend_class, $this->registry['all_backends']);
      $this->registry['used_backends'][$bin] = $backend;
    }
    if (count(array_unique(array_values($this->registry['used_backends']))) == 1 &&
    array_values($this->registry['used_backends'])[0] == 'cache.backend.database') {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN;
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO;
  }

}
