<?php
/**
 * @file
 * Contains \SiteAudit\Check\Views\CacheResults.
 */

class SiteAuditCheckViewsCacheResults extends SiteAuditCheckAbstract {
  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Query results caching');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Check the length of time raw query results should be cached.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {
    return dt('No View is caching query results!');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultInfo().
   */
  public function getResultInfo() {
    return $this->getResultWarn();
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultPass().
   */
  public function getResultPass() {
    if ($this->registry['views_cache_bully_results']) {
      return dt('Views Cache Bully is enforcing query result caching.');
    }
    return dt('Every View is caching query results.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultWarn().
   */
  public function getResultWarn() {
    return dt('The following Views are not caching query results: @views_without_results_caching', array(
      '@views_without_results_caching' => implode(', ', $this->registry['views_without_results_caching']),
    ));
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getAction().
   */
  public function getAction() {
    if (!in_array($this->score, array(SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO, SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS))) {
      $ret_val = dt('Query results should be cached for at least 1 minute.');
      if (drush_get_option('detail')) {
        $steps = array(
          dt('Go to /admin/structure/views/'),
          dt('Edit the View in question'),
          dt('Select the Display'),
          dt('Click Advanced'),
          dt('Next to Caching, click to edit.'),
          dt('Query results: (something other than Never cache)'),
        );
        if (drush_get_option('html')) {
          $ret_val .= '<ol><li>' . implode('</li><li>', $steps) . '</li></ol>';
        }
        else {
          foreach ($steps as $step) {
            $ret_val .= PHP_EOL;
            if (!drush_get_option('json')) {
              $ret_val .= str_repeat(' ', 8);
            }
            $ret_val .= '- ' . $step;
          }
        }
      }
      return $ret_val;
    }
  }

  /**
   * Implements \SiteAudit\Check\Abstract\calculateScore().
   */
  public function calculateScore() {
    // Views Cache Bully.
    $this->registry['views_cache_bully_results'] = FALSE;
    if (module_exists('views_cache_bully') && variable_get('views_cache_bully_results_lifespan', 3600) > 0) {
      $this->registry['views_cache_bully_results'] = TRUE;
    }
    if ($this->registry['views_cache_bully_results']) {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS;
    }

    $this->registry['results_lifespan'] = array();
    foreach ($this->registry['views'] as $view) {
      foreach ($view->display as $display_name => $display) {
        if (!isset($display->disabled) || !$display->disabled) {
          // Default display OR overriding display.
          if (isset($display->display_options['cache'])) {
            if ($display->display_options['cache']['type'] == 'none' || ($display->display_options['cache'] == '')) {
              if ($display_name == 'default') {
                $this->registry['results_lifespan'][$view->name]['default'] = 'none';
              }
              else {
                $this->registry['results_lifespan'][$view->name]['displays'][$display_name] = 'none';
              }
            }
            else {
              if ($display->display_options['cache']['results_lifespan'] == 'custom') {
                $lifespan = $display->display_options['cache']['results_lifespan_custom'];
              }
              else {
                $lifespan = $display->display_options['cache']['results_lifespan'];
              }
              if ($lifespan < 1) {
                $lifespan = 'none';
              }
              if ($display_name == 'default') {
                $this->registry['results_lifespan'][$view->name]['default'] = $lifespan;
              }
              else {
                $this->registry['results_lifespan'][$view->name]['displays'][$display_name] = $lifespan;
              }
            }
          }
          // Display is using default display's caching.
          else {
            $this->registry['results_lifespan'][$view->name]['displays'][$display_name] = 'default';
          }
        }
      }
    }

    $this->registry['views_without_results_caching'] = array();

    foreach ($this->registry['results_lifespan'] as $view_name => $view_data) {
      // Views with only master display.
      if (!isset($view_data['displays']) || (count($view_data['displays']) == 0)) {
        if ($view_data['default'] == 'none') {
          $this->registry['views_without_results_caching'][] = $view_name;
        }
      }
      else {
        // If all the displays are default, consolidate.
        $all_default_displays = TRUE;
        foreach ($view_data['displays'] as $display_name => $lifespan) {
          if ($lifespan != 'default') {
            $all_default_displays = FALSE;
          }
        }
        if ($all_default_displays) {
          if ($view_data['default'] == 'none') {
            $this->registry['views_without_results_caching'][] = $view_name;
          }
        }
        else {
          $uncached_view_string = $view_name;
          $uncached_view_displays = array();
          foreach ($view_data['displays'] as $display_name => $display_data) {
            if ($display_data == 'none' || ($display_data == 'default' && $view_data['default'] == 'none')) {
              $uncached_view_displays[] = $display_name;
            }
          }
          if (!empty($uncached_view_displays)) {
            $uncached_view_string .= ' (' . implode(', ', $uncached_view_displays) . ')';
            $this->registry['views_without_results_caching'][] = $uncached_view_string;
          }
        }
      }
    }

    if (count($this->registry['views_without_results_caching']) == 0) {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS;
    }
    if (site_audit_env_is_dev()) {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO;
    }
    if (count($this->registry['views_without_results_caching']) == count($this->registry['views'])) {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL;
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN;
  }
}
