<?php
/**
 * @file
 * Contains \AuditCheckExtensionsMissing.
 */

class AuditCheckExtensionsMissing extends AuditCheck {
  /**
   * Implements \AuditCheck\getLabel().
   */
  public function getLabel() {
    return dt('Missing');
  }

  /**
   * Implements \AuditCheck\getDescription().
   */
  public function getDescription() {
    return dt('Detect missing extensions (modules and themes) in a site, which degrades performance.');
  }

  /**
   * Implements \AuditCheck\getResultFail().
   */
  public function getResultFail() {
    return dt('The following extensions are missing from your codebase: @extensions_missing', array(
      '@extensions_missing' => implode(', ', $this->registry['extensions_missing']),
    ));
  }

  /**
   * Implements \AuditCheck\getResultInfo().
   */
  public function getResultInfo() {}

  /**
   * Implements \AuditCheck\getResultPass().
   */
  public function getResultPass() {
    return dt('No extensions are missing from your codebase.');
  }

  /**
   * Implements \AuditCheck\getResultWarning().
   */
  public function getResultWarning() {}

  /**
   * Implements \AuditCheck\getAction().
   */
  public function getAction() {
    if ($this->score != AuditCheck::AUDIT_CHECK_SCORE_PASS) {
      return dt('Download the extensions to your codebase, disable, uninstall, then remove the code.');
    }
  }

  /**
   * Implements \AuditCheck\getScore().
   */
  public function getScore() {
    $this->registry['extensions_missing'] = array();
    $drupal_root = drush_get_context('DRUSH_SELECTED_DRUPAL_ROOT');

    $result = db_select('system')->fields('system', array(
      'name',
      'filename',
    ))->condition('status', '1', '=')->execute();

    foreach ($result as $row) {
      if (!file_exists($drupal_root . '/' . $row->filename)) {
        $this->registry['extensions_missing'][] = $row->name;
      }
    }
    if (!empty($this->registry['extensions_missing'])) {
      return AuditCheck::AUDIT_CHECK_SCORE_FAIL;
    }
    return AuditCheck::AUDIT_CHECK_SCORE_PASS;
  }
}
