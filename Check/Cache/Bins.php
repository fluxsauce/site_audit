<?php
/**
 * @file
 * Contains \SiteAudit\Check\Cache\Bins.
 */

class SiteAuditCheckCacheBins extends SiteAuditCheckAbstract {
  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Cache bins');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Detail explicitly defined cache bins.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getResultInfo().
   */
  public function getResultInfo() {
    if (!empty($this->registry['cache_bins'])) {
      if (drush_get_option('html')) {
        $ret_val = '<table class="table table-condensed">';
        $ret_val .= '<thead><tr><th>Bin</th><th>Class</th></tr></thead>';
        $ret_val .= '<tbody>';
        foreach ($this->registry['cache_bins'] as $bin => $class) {
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
        foreach ($this->registry['cache_bins'] as $bin => $class) {
          $ret_val .= PHP_EOL;
          if (!drush_get_option('json')) {
            $ret_val .= str_repeat(' ', 4);
          }
          $ret_val .= "$bin: $class";
        }
      }
      return $ret_val;
    }
    else {
      return dt('No cache bins defined.');
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
    $this->registry['cache_bins'] = array();
    $variables = preg_grep('/^cache\./', array_values($services));
    if (!empty($variables)) {
      foreach ($variables as $variable_name) {
        if (!preg_match('/\.backend\./', $variable_name)) {
          $this->registry['cache_bins'][explode('.', $variable_name)[1]] = get_class($container->get($variable_name));
        }
      }
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO;
  }

}
