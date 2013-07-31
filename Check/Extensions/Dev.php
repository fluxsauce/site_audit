<?php
/**
 * @file
 * Contains \SiteAudit\Check\Extensions\Dev.
 */

class SiteAuditCheckExtensionsDev extends SiteAuditCheckAbstract {
  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Development');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Check for enabled development modules.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getResultInfo().
   */
  public function getResultInfo() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getResultPass().
   */
  public function getResultPass() {
    return dt('No enabled development extensions were detected; no action required.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultWarn().
   */
  public function getResultWarn() {
    $ret_val = dt('The following development modules(s) are currently enabled: @list', array(
      '@list' => implode(', ', array_keys($this->registry['extensions_dev'])),
    ));
    if (drush_get_option('detail')) {
      if (drush_get_option('html')) {
        $ret_val = '<p>' . $ret_val . '</p>';
        $ret_val .= '<table class="table table-condensed">';
        $ret_val .= '<thead><tr><th>Name</th><th>Reason</th></thead>';
        $ret_val .= '<tbody>';
        foreach ($this->registry['extensions_dev'] as $row) {
          $ret_val .= '<tr><td>' . implode('</td><td>', $row) . '</td></tr>';
        }
        $ret_val .= '</tbody>';
        $ret_val .= '</table>';
      }
      else {
        foreach ($this->registry['extensions_dev'] as $row) {
          $ret_val .= PHP_EOL . str_repeat(' ', 6) . '- ' . $row[0] . ': ' . $row[1];
        }
      }
    }
    return $ret_val;
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getAction().
   */
  public function getAction() {
    if ($this->getScore() != SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS) {
      return dt('Disable development modules for increased performance, stability and security in the Live (production) environment.');
    }
  }

  /**
   * Implements \SiteAudit\Check\Abstract\calculateScore().
   */
  public function calculateScore() {
    $this->registry['extensions_dev'] = array();
    $extension_info = drush_get_extensions(FALSE);
    uasort($extension_info, '_drush_pm_sort_extensions');
    $dev_extensions = $this->getExtensions();

    foreach ($extension_info as $key => $extension) {
      $row = array();
      $status = drush_get_extension_status($extension);
      // Only enabled extensions.
      if (!in_array($status, array('enabled'))) {
        unset($extension_info[$key]);
        continue;
      }

      if (!array_key_exists($extension->name, $dev_extensions)) {
        unset($extension_info[$key]);
        continue;
      }

      // Name.
      $row[] = $extension->label;
      // Reason.
      $row[] = $dev_extensions[$extension->name];

      $this->registry['extensions_dev'][$extension->name] = $row;
    }

    if (!empty($this->registry['extensions_dev'])) {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN;
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS;
  }

  /**
   * Get a list of development extension names and reasons.
   * @return array
   *   Keyed by module machine name, value is explanation.
   */
  public function getExtensions() {
    $developer_modules = array(
      'module_builder' => dt('Development utility.'),
      'form' => dt('Development utility.'),
      'drupal_ipsum' => dt('Development utility to generate fake content.'),
      'dummy_content' => dt('Development utility to generate random content.'),
      // Examples module.
      'action_example' => dt('Development examples.'),
      'ajax_example' => dt('Development examples.'),
      'batch_example' => dt('Development examples.'),
      'block_example' => dt('Development examples.'),
      'cache_example' => dt('Development examples.'),
      'contextual_links_example' => dt('Development examples.'),
      'cron_example' => dt('Development examples.'),
      'dbtng_example' => dt('Development examples.'),
      'email_example' => dt('Development examples.'),
      'entity_example' => dt('Development examples.'),
      'examples' => dt('Development examples.'),
      'field_example' => dt('Development examples.'),
      'field_permission_example' => dt('Development examples.'),
      'file_example' => dt('Development examples.'),
      'filter_example' => dt('Development examples.'),
      'form_example' => dt('Development examples.'),
      'image_example' => dt('Development examples.'),
      'js_example' => dt('Development examples.'),
      'menu_example' => dt('Development examples.'),
      'node_access_example' => dt('Development examples.'),
      'node_example' => dt('Development examples.'),
      'nodeapi_example' => dt('Development examples.'),
      'page_example' => dt('Development examples.'),
      'pager_example' => dt('Development examples.'),
      'queue_example' => dt('Development examples.'),
      'rdf_example' => dt('Development examples.'),
      'render_example' => dt('Development examples.'),
      'simpletest_example' => dt('Development examples.'),
      'tabledrag_example' => dt('Development examples.'),
      'tablesort_example' => dt('Development examples.'),
      'theming_example' => dt('Development examples.'),
      'token_example' => dt('Development examples.'),
      'trigger_example' => dt('Development examples.'),
      'vertical_tabs_example' => dt('Development examples.'),
      'xmlrpc_example' => dt('Development examples.'),
    );

    // From http://drupal.org/project/admin_menu admin_menu.inc in function
    // _admin_menu_developer_modules().
    $admin_menu_developer_modules = array(
      'admin_devel' => dt('Debugging utility; degrades performance.'),
      'cache_disable' => dt('Development utility and performance drain; degrades performance.'),
      'coder' => dt('Debugging utility; potential security risk and unnecessary performance hit.'),
      'content_copy' => dt('Development utility; unnecessary overhead.'),
      'context_ui' => dt('Development user interface; unnecessary overhead.'),
      'debug' => dt('Debugging utility; potential security risk, unnecessary overhead.'),
      'delete_all' => dt('Development utility; potentially dangerous.'),
      'demo' => dt('Development utility for sandboxing.'),
      'devel' => dt('Debugging utility; degrades performance and potential security risk.'),
      'devel_node_access' => dt('Development utility; degrades performance and potential security risk.'),
      'devel_themer' => dt('Development utility; degrades performance and potential security risk.'),
      'field_ui' => dt('Development user interface; unnecessary overhead.'),
      'fontyourface_ui' => dt('Development user interface; unnecessary overhead.'),
      'form_controller' => dt('Development utility; unnecessary overhead.'),
      'imagecache_ui' => dt('Development user interface; unnecessary overhead.'),
      'journal' => dt('Development utility; unnecessary overhead.'),
      'l10n_client' => dt('Development utility; unnecessary overhead.'),
      'l10n_update' => dt('Development utility; unnecessary overhead.'),
      'macro' => dt('Development utility; unnecessary overhead.'),
      'rules_admin' => dt('Development user interface; unnecessary overhead.'),
      'stringoverrides' => dt('Development utility.'),
      'trace' => dt('Debugging utility; degrades performance and potential security risk.'),
      'upgrade_status' => dt('Development utility for performing a major Drupal core update; should removed after use.'),
      'user_display_ui' => dt('Development user interface; unnecessary overhead.'),
      'util' => dt('Development utility; unnecessary overhead, potential security risk.'),
      'views_ui' => dt('Development user interface; unnecessary overhead.'),
      'views_theme_wizard' => dt('Development utility; unnecessary overhead, potential security risk.'),
    );

    return array_merge($admin_menu_developer_modules, $developer_modules);
  }
}
