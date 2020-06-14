<?php

namespace Drupal\site_audit\Plugin\SiteAuditCheck;

use Drupal\site_audit\Plugin\SiteAuditCheckBase;

/**
 * Provides the ExtensionsDev Check.
 *
 * @SiteAuditCheck(
 *  id = "extensions_dev",
 *  name = @Translation("Development"),
 *  description = @Translation("Check for enabled development modules."),
 *  report = "extensions"
 * )
 */
class ExtensionsDev extends SiteAuditCheckBase {

  /**
   * {@inheritdoc}.
   */
  public function getResultFail() {}

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
    return $this->t('No enabled development extensions were detected; no action required.');
  }

  /**
   * {@inheritdoc}.
   */
  public function getResultWarn() {
    $ret_val = $this->t('The following development modules(s) are currently enabled: @list', [
      '@list' => implode(', ', array_keys($this->registry->extensions_dev)),
    ]);
    $show_table = TRUE;
    if (site_audit_env_is_dev()) {
      $show_table = FALSE;
    }

    $table_rows = [];
    foreach ($this->registry->extensions_dev as $row) {
      $table_rows[] = $row;
    }

    $header = [
      $this->t('Name'),
      $this->t('Reason'),
    ];
    return [
      '#theme' => 'table',
      '#class' => 'table-condensed',
      '#header' => $header,
      '#rows' => $table_rows,
    ];
  }

  /**
   * {@inheritdoc}.
   */
  public function getAction() {
    if ($this->getScore() == SiteAuditCheckBase::AUDIT_CHECK_SCORE_WARN) {
      $show_action = TRUE;
      if (site_audit_env_is_dev()) {
        $show_action = FALSE;
      }
      if ($show_action) {
        return $this->t('Disable development modules for increased stability, security and performance in the Live (production) environment.');
      }
    }
  }

  /**
   * {@inheritdoc}.
   */
  public function calculateScore() {
    if (!isset($this->registry->extensions) || empty($this->registry->extensions)) {
      $moduleHandler = \Drupal::service('module_handler');
      $this->registry->extensions = $modules = system_rebuild_module_data();
      uasort($this->registry->extensions, 'system_sort_modules_by_info_name');
    }
    $this->registry->extensions_dev = [];
    $extension_info = $this->registry->extensions;
    // uasort($extension_info, '_drush_pm_sort_extensions');.
    $dev_extensions = $this->getExtensions();
    foreach ($extension_info as $key => $extension) {
      $row = [];
      // Not in the list of known development modules.
      if (!array_key_exists($extension->getName(), $dev_extensions)) {
        unset($extension_info[$key]);
        continue;
      }

      // Do not report modules that are dependencies of other modules, such
      // as field_ui in Drupal Commerce.
      if (isset($extension->required_by) && !empty($extension->required_by)) {
        unset($extension_info[$key]);
        continue;
      }

      // Name.
      $row[] = $extension->getName();
      // Reason.
      $row[] = $dev_extensions[$extension->getName()];

      $this->registry->extensions_dev[$extension->getName()] = $row;
    }

    if (!empty($this->registry->extensions_dev)) {
      if (site_audit_env_is_dev()) {
        return SiteAuditCheckBase::AUDIT_CHECK_SCORE_INFO;
      }
      return SiteAuditCheckBase::AUDIT_CHECK_SCORE_WARN;
    }
    return SiteAuditCheckBase::AUDIT_CHECK_SCORE_PASS;
  }

  /**
   * Get a list of development extension names and reasons.
   *
   * @return array
   *   Keyed by module machine name, value is explanation.
   */
  public function getExtensions() {
    $developer_modules = [
      'ipsum' => $this->t('Development utility to generate fake content.'),
      'testmodule' => $this->t('Internal test module.'),
      // Examples module.
      'block_example' => $this->t('Development examples.'),
      'cache_example' => $this->t('Development examples.'),
      'config_entity_example' => $this->t('Development examples.'),
      'content_entity_example' => $this->t('Development examples.'),
      'dbtng_example' => $this->t('Development examples.'),
      'email_example' => $this->t('Development examples.'),
      'examples' => $this->t('Development examples.'),
      'field_example' => $this->t('Development examples.'),
      'field_permission_example' => $this->t('Development examples.'),
      'file_example' => $this->t('Development examples.'),
      'js_example' => $this->t('Development examples.'),
      'node_type_example' => $this->t('Development examples.'),
      'page_example' => $this->t('Development examples.'),
      'phpunit_example' => $this->t('Development examples.'),
      'simpletest_example' => $this->t('Development examples.'),
      'tablesort_example' => $this->t('Development examples.'),
      'tour_example' => $this->t('Development examples.'),
    ];

    // From http://drupal.org/project/admin_menu admin_menu.inc in function
    // _admin_menu_developer_modules().
    $admin_menu_developer_modules = [
      'admin_devel' => $this->t('Debugging utility; degrades performance.'),
      'cache_disable' => $this->t('Development utility and performance drain; degrades performance.'),
      'coder' => $this->t('Debugging utility; potential security risk and unnecessary performance hit.'),
      'content_copy' => $this->t('Development utility; unnecessary overhead.'),
      'context_ui' => $this->t('Development user interface; unnecessary overhead.'),
      'debug' => $this->t('Debugging utility; potential security risk, unnecessary overhead.'),
      'delete_all' => $this->t('Development utility; potentially dangerous.'),
      'demo' => $this->t('Development utility for sandboxing.'),
      'devel' => $this->t('Debugging utility; degrades performance and potential security risk.'),
      'devel_node_access' => $this->t('Development utility; degrades performance and potential security risk.'),
      'devel_themer' => $this->t('Development utility; degrades performance and potential security risk.'),
      'field_ui' => $this->t(
      'Development user interface; allows privileged users to change site 
structure which can lead to data inconsistencies. Best practice is to 
store Content Types in code and deploy changes instead of allowing
editing in live environments.'),
      'fontyourface_ui' => $this->t('Development user interface; unnecessary overhead.'),
      'form_controller' => $this->t('Development utility; unnecessary overhead.'),
      'imagecache_ui' => $this->t('Development user interface; unnecessary overhead.'),
      'journal' => $this->t('Development utility; unnecessary overhead.'),
      'l10n_client' => $this->t('Development utility; unnecessary overhead.'),
      'l10n_update' => $this->t('Development utility; unnecessary overhead.'),
      'macro' => $this->t('Development utility; unnecessary overhead.'),
      'rules_admin' => $this->t('Development user interface; unnecessary overhead.'),
      'stringoverrides' => $this->t('Development utility.'),
      'trace' => $this->t('Debugging utility; degrades performance and potential security risk.'),
      'upgrade_status' => $this->t('Development utility for performing a major Drupal core update; should removed after use.'),
      'user_display_ui' => $this->t('Development user interface; unnecessary overhead.'),
      'util' => $this->t('Development utility; unnecessary overhead, potential security risk.'),
      'views_ui' => $this->t(
      'Development UI; allows privileged users to change site structure which
can lead to performance problems or inconsistent behavior. Best practice 
is to store Views in code and deploy changes instead of allowing editing 
in live environments.'),
      'views_theme_wizard' => $this->t('Development utility; unnecessary overhead, potential security risk.'),
    ];

    return array_merge($admin_menu_developer_modules, $developer_modules);
  }

}
