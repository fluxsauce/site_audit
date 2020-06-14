<?php

namespace Drupal\site_audit\Plugin\SiteAuditCheck;

use Drupal\site_audit\Plugin\SiteAuditCheckBase;

/**
 * Provides the ViewsCacheResults Check.
 *
 * @SiteAuditCheck(
 *  id = "views_cache_results",
 *  name = @Translation("Query results caching"),
 *  description = @Translation("Check to see if raw query results are being cached."),
 *  report = "views"
 * )
 */
class ViewsCacheResults extends SiteAuditCheckBase {

  /**
   * {@inheritdoc}.
   */
  public function getResultFail() {
    return $this->t('No View is caching query results!');
  }

  /**
   * {@inheritdoc}.
   */
  public function getResultInfo() {
    return $this->getResultWarn();
  }

  /**
   * {@inheritdoc}.
   */
  public function getResultPass() {
    return $this->t('Every View is caching query results.');
  }

  /**
   * {@inheritdoc}.
   */
  public function getResultWarn() {
    return $this->t('The following Views are not caching query results: @views_without_results_caching', [
      '@views_without_results_caching' => implode(', ', $this->registry->views_without_results_caching),
    ]);
  }

  /**
   * {@inheritdoc}.
   */
  public function getAction() {
    if (!in_array($this->score, [SiteAuditCheckBase::AUDIT_CHECK_SCORE_INFO, SiteAuditCheckBase::AUDIT_CHECK_SCORE_PASS])) {
      $steps = [
        $this->t('Go to /admin/structure/views/'),
        $this->t('Edit the View in question'),
        $this->t('Select the Display'),
        $this->t('Click Advanced'),
        $this->t('Next to Caching, click to edit.'),
        $this->t('Caching: (something other than None)'),
      ];

      return [
        '#theme' => 'item_list',
        '#title' => $this->t('Query results should be cached for at least 1 minute or use tag caching.'),
        // '#description' => $this->t(),
        '#items' => $steps,
        '#list_type' => 'ol',
      ];
    }
  }

  /**
   * {@inheritdoc}.
   */
  public function calculateScore() {
    $this->registry->results_lifespan = [];
    if (empty($this->registry->views)) {
      $this->checkInvokeCalculateScore('views_count');
    }
    foreach ($this->registry->views as $view) {
      // Skip views used for administration purposes.
      if (in_array($view->get('tag'), ['admin', 'commerce'])) {
        continue;
      }
      foreach ($view->get('display') as $display_name => $display) {
        if (!isset($display['display_options']['enabled']) || $display['display_options']['enabled']) {
          // Default display OR overriding display.
          if (isset($display['display_options']['cache'])) {
            if ($display['display_options']['cache']['type'] == 'none' || ($display['display_options']['cache'] == '')) {
              if ($display_name == 'default') {
                $this->registry->results_lifespan[$view->get('id')]['default'] = 'none';
              }
              else {
                $this->registry->results_lifespan[$view->get('id')]['displays'][$display_name] = 'none';
              }
            }
            elseif ($display['display_options']['cache']['type'] == 'time') {
              if ($display['display_options']['cache']['options']['results_lifespan'] == 0) {
                $lifespan = $display['display_options']['cache']['options']['results_lifespan_custom'];
              }
              else {
                $lifespan = $display['display_options']['cache']['options']['results_lifespan'];
              }
              if ($lifespan < 1) {
                $lifespan = 'none';
              }
              if ($display_name == 'default') {
                $this->registry->results_lifespan[$view->get('id')]['default'] = $lifespan;
              }
              else {
                $this->registry->results_lifespan[$view->get('id')]['displays'][$display_name] = $lifespan;
              }
            }
            elseif ($display['display_options']['cache']['type'] == 'tag') {
              if ($display_name == 'default') {
                $this->registry->results_lifespan[$view->get('id')]['default'] = 'tag';
              }
              else {
                $this->registry->results_lifespan[$view->get('id')]['displays'][$display_name] = 'tag';
              }
            }
          }
          // Display is using default display's caching.
          else {
            $this->registry->results_lifespan[$view->get('id')]['displays'][$display_name] = 'default';
          }
        }
      }
    }
    $this->registry->views_without_results_caching = [];

    foreach ($this->registry->results_lifespan as $view_name => $view_data) {
      // Views with only master display.
      if (!isset($view_data['displays']) || (count($view_data['displays']) == 0)) {
        if ($view_data['default'] == 'none') {
          $this->registry->views_without_results_caching[] = $view_name;
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
          if (isset($view_data['default'])) {
            if ($view_data['default'] == 'none') {
              $this->registry->views_without_results_caching[] = $view_name;
            }
          }
        }
        else {
          $uncached_view_string = $view_name;
          $uncached_view_displays = [];
          foreach ($view_data['displays'] as $display_name => $display_data) {
            if ($display_data == 'none' || ($display_data == 'default' && $view_data['default'] == 'none')) {
              $uncached_view_displays[] = $display_name;
            }
          }
          if (!empty($uncached_view_displays)) {
            $uncached_view_string .= ' (' . implode(', ', $uncached_view_displays) . ')';
            $this->registry->views_without_results_caching[] = $uncached_view_string;
          }
        }
      }
    }

    if (count($this->registry->views_without_results_caching) == 0) {
      return SiteAuditCheckBase::AUDIT_CHECK_SCORE_PASS;
    }
    if (site_audit_env_is_dev()) {
      return SiteAuditCheckBase::AUDIT_CHECK_SCORE_INFO;
    }
    if (count($this->registry->views_without_results_caching) == count($this->registry->views)) {
      return SiteAuditCheckBase::AUDIT_CHECK_SCORE_FAIL;
    }
    return SiteAuditCheckBase::AUDIT_CHECK_SCORE_WARN;
  }

}
