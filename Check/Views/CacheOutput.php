<?php
/**
 * @file
 * Contains \SiteAudit\Check\Views\CacheOutput.
 */

/**
 * Class SiteAuditCheckViewsCacheOutput.
 */
class SiteAuditCheckViewsCacheOutput extends SiteAuditCheckAbstract {

  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Rendered output caching');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Check to see if raw rendered output is being cached.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {
    return dt('No View is caching rendered output!');
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
    return dt('Every View is caching rendered output.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultWarn().
   */
  public function getResultWarn() {
    return dt('The following Views are not caching rendered output: @views_without_output_caching', array(
      '@views_without_output_caching' => implode(', ', $this->registry['views_without_output_caching']),
    ));
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getAction().
   */
  public function getAction() {
    if (!in_array($this->score, array(SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO, SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS))) {
      $ret_val = dt('Rendered output should be cached for as long as possible (if the query changes, the output will be refreshed).');
      if (drush_get_option('detail')) {
        $steps = array(
          dt('Go to /admin/structure/views/'),
          dt('Edit the View in question'),
          dt('Select the Display'),
          dt('Click Advanced'),
          dt('Next to Caching, click to edit.'),
          dt('Caching: (something other than None)'),
        );
        if (drush_get_option('html') == TRUE) {
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
    $this->registry['output_lifespan'] = array();
    foreach ($this->registry['views'] as $view) {
      // Skip views used for administration purposes.
      if (in_array($view->get('tag'), array('admin', 'commerce'))) {
        continue;
      }
      foreach ($view->get('display') as $display_name => $display) {
        if (!isset($display['display_options']['enabled']) || $display['display_options']['enabled']) {
          // Default display OR overriding display.
          if (isset($display['display_options']['cache'])) {
            if ($display['display_options']['cache']['type'] == 'none' || ($display['display_options']['cache'] == '')) {
              if ($display_name == 'default') {
                $this->registry['output_lifespan'][$view->get('id')]['default'] = 'none';
              }
              else {
                $this->registry['output_lifespan'][$view->get('id')]['displays'][$display_name] = 'none';
              }
            }
            elseif ($display['display_options']['cache']['type'] == 'time') {
              if ($display['display_options']['cache']['options']['output_lifespan'] == 0) {
                $lifespan = $display['display_options']['cache']['options']['output_lifespan_custom'];
              }
              else {
                $lifespan = $display['display_options']['cache']['options']['output_lifespan'];
              }
              if ($lifespan < 1) {
                $lifespan = 'none';
              }
              if ($display_name == 'default') {
                $this->registry['output_lifespan'][$view->get('id')]['default'] = $lifespan;
              }
              else {
                $this->registry['output_lifespan'][$view->get('id')]['displays'][$display_name] = $lifespan;
              }
            }
            elseif ($display['display_options']['cache']['type'] == 'tag') {
              if ($display_name == 'default') {
                $this->registry['output_lifespan'][$view->get('id')]['default'] = 'tag';
              }
              else {
                $this->registry['output_lifespan'][$view->get('id')]['displays'][$display_name] = 'tag';
              }
            }
          }
          // Display is using default display's caching.
          else {
            $this->registry['output_lifespan'][$view->get('id')]['displays'][$display_name] = 'default';
          }
        }
      }
    }

    $this->registry['views_without_output_caching'] = array();

    foreach ($this->registry['output_lifespan'] as $view_name => $view_data) {
      // Views with only master display.
      if (!isset($view_data['displays']) || (count($view_data['displays']) == 0)) {
        if ($view_data['default'] == 'none') {
          $this->registry['views_without_output_caching'][] = $view_name;
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
            $this->registry['views_without_output_caching'][] = $view_name;
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
            $this->registry['views_without_output_caching'][] = $uncached_view_string;
          }
        }
      }
    }

    if (count($this->registry['views_without_output_caching']) == 0) {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS;
    }
    if (site_audit_env_is_dev()) {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO;
    }
    if (count($this->registry['views_without_output_caching']) == count($this->registry['views'])) {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL;
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN;
  }

}
