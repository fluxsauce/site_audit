<?php

namespace Drupal\site_audit\Plugin\SiteAuditCheck;

use Drupal\site_audit\Plugin\SiteAuditCheckBase;

/**
 * Provides the ExtensionsUnrecommended Check.
 *
 * @SiteAuditCheck(
 *  id = "extensions_unrecommended",
 *  name = @Translation("Not recommended"),
 *  description = @Translation("Check for unrecommended modules."),
 *  report = "extensions"
 * )
 */
class ExtensionsUnrecommended extends SiteAuditCheckBase {

  /**
   * {@inheritdoc}.
   */
  public function getResultFail() {
    $ret_val = $this->t('The following unrecommended modules(s) currently exist in your codebase: @list', [
      '@list' => implode(', ', array_keys($this->registry->extensions_unrec)),
    ]);
    // If ($this->options['detail']) {.
    if (TRUE) {
      // If ($this->options['html']) {.
      if (TRUE) {
        $ret_val .= '<br/>';
        $ret_val .= '<table class="table table-condensed">';
        $ret_val .= '<thead><tr><th>' . $this->t('Name') . '</th><th>' . $this->t('Reason') . '</th></thead>';
        $ret_val .= '<tbody>';
        foreach ($this->registry->extensions_unrec as $row) {
          $ret_val .= '<tr><td>' . implode('</td><td>', $row) . '</td></tr>';
        }
        $ret_val .= '</tbody>';
        $ret_val .= '</table>';
      }
      else {
        foreach ($this->registry->extensions_unrec as $row) {
          $ret_val .= PHP_EOL;
          if (!$this->options['json']) {
            $ret_val .= str_repeat(' ', 6);
          }
          $ret_val .= '- ' . $row[0] . ': ' . $row[1];
        }
      }
    }
    return $ret_val;
  }

  /**
   * {@inheritdoc}.
   */
  public function getResultInfo() {}

  /**
   * {@inheritdoc}.
   */
  public function getResultPass() {
    return $this->t('No unrecommended extensions were detected; no action required.', []);
  }

  /**
   * {@inheritdoc}.
   */
  public function getResultWarn() {
    return $this->t('There are @extension_count extensions enabled; that\'s higher than the average.', [
      '@extension_count' => $this->registry->extension_count,
    ]);
  }

  /**
   * {@inheritdoc}.
   */
  public function getAction() {
    if ($this->getScore() != SiteAuditCheckBase::AUDIT_CHECK_SCORE_PASS) {
      return $this->t('Disable and completely remove unrecommended modules from your codebase for increased performance, stability and security in the any environment.');
    }
  }

  /**
   * {@inheritdoc}.
   */
  public function calculateScore() {
    $this->registry->extensions_unrec = [];
    if (!isset($this->registry->extensions)) {
      $this->checkInvokeCalculateScore('extensions_count');
    }
    $extension_info = $this->registry->extensions;
    // uasort($extension_info, '_drush_pm_sort_extensions');.
    $unrecommended_extensions = $this->getExtensions();

    foreach ($extension_info as $extension) {
      $row = [];

      $machine_name = $extension->getName();

      // Not in the list of known unrecommended modules.
      if (!array_key_exists($machine_name, $unrecommended_extensions)) {
        continue;
      }

      // Name.
      $row[] = $extension->label;
      // Reason.
      $row[] = $unrecommended_extensions[$machine_name];

      $this->registry->extensions_unrec[$machine_name] = $row;
    }

    if (!empty($this->registry->extensions_unrec)) {
      return SiteAuditCheckBase::AUDIT_CHECK_SCORE_FAIL;
    }
    return SiteAuditCheckBase::AUDIT_CHECK_SCORE_PASS;
  }

  /**
   * Get a list of unrecommended extension names and reasons.
   *
   * @return array
   *   Keyed by module machine name, value is explanation.
   */
  public function getExtensions() {
    $unrecommended_modules = [
      'bad_judgement' => $this->t('Joke module, framework for anarchy.'),
      'php' => $this->t('Executable code should never be stored in the database.'),
    ];
    // If ($this->options['vendor'] == 'pantheon') { TODO.
    if (FALSE) {
      // Unsupported or redundant.
      $pantheon_unrecommended_modules = [
        'memcache' => dt('Pantheon does not provide memcache; instead, redis is provided as a service to all customers; see http://helpdesk.getpantheon.com/customer/portal/articles/401317'),
        'memcache_storage' => dt('Pantheon does not provide memcache; instead, redis is provided as a service to all customers; see http://helpdesk.getpantheon.com/customer/portal/articles/401317'),
      ];
      $unrecommended_modules = array_merge($unrecommended_modules, $pantheon_unrecommended_modules);
    }
    return $unrecommended_modules;
  }

}
