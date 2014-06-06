<?php
/**
 * @file
 * Contains \SiteAudit\Check\Extensions\Disabled.
 */

class SiteAuditCheckExtensionsDisabled extends SiteAuditCheckAbstract {
  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Disabled modules');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Detect modules that have been disabled, but have not been uninstalled.');
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
    return dt('No modules need to be uninstalled.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultWarn().
   */
  public function getResultWarn() {
    return dt('The following modules have been disabled, but have not yet been uninstalled: @extensions_disabled', array(
      '@extensions_disabled' => implode(', ', $this->registry['extensions_disabled']),
    ));
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getAction().
   */
  public function getAction() {
    if ($this->score != SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS) {
      return dt('Uninstall unused modules; if not in core, remove them from the codebase after uninstalling.');
    }
  }

  /**
   * Implements \SiteAudit\Check\Abstract\calculateScore().
   */
  public function calculateScore() {
    $this->registry['extensions_disabled'] = array();

    foreach ($this->registry['extensions'] as $extension) {
      if (($extension->type == 'module') && (drush_get_extension_status($extension) == 'disabled')) {
        $this->registry['extensions_disabled'][] = $extension->name;
      }
    }

    if (!empty($this->registry['extensions_disabled'])) {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN;
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS;
  }
}
