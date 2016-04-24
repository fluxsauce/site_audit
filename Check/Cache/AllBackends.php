<?php
/**
 * @file
 * Contains \SiteAudit\Check\Cache\AllBackends.
 */

/**
 * Class SiteAuditCheckCacheAllBackends.
 */
class SiteAuditCheckCacheAllBackends extends SiteAuditCheckAbstract {

  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Available Caching backends');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Detail all available caching backends.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getResultInfo().
   */
  public function getResultInfo() {
    if (!empty($this->registry['all_backends'])) {
      if (drush_get_option('html')) {
        $ret_val = '<table class="table table-condensed">';
        $ret_val .= '<thead><tr><th>Backend</th><th>Class</th></tr></thead>';
        $ret_val .= '<tbody>';
        foreach ($this->registry['all_backends'] as $backend => $class) {
          $ret_val .= "<tr><td>$backend</td><td>$class</td></tr>";
        }
        $ret_val .= '</tbody>';
        $ret_val .= '</table>';
      }
      else {
        $ret_val  = 'Backend: Class' . PHP_EOL;
        if (!drush_get_option('json')) {
          $ret_val .= str_repeat(' ', 4);
        }
        $ret_val .= '----------';
        foreach ($this->registry['all_backends'] as $backend => $class) {
          $ret_val .= PHP_EOL;
          if (!drush_get_option('json')) {
            $ret_val .= str_repeat(' ', 4);
          }
          $ret_val .= "$backend: $class";
        }
      }
      return $ret_val;
    }
    else {
      return dt('No cache backend found.');
    }
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
    $services = $container->getServiceIds();
    $this->registry['all_backends'] = array();
    $backends = preg_grep('/^cache\.backend\./', array_values($services));
    foreach ($backends as $backend) {
      $this->registry['all_backends'][$backend] = get_class($container->get($backend));
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO;
  }

}
