<?php
/**
 * @file
 * Contains \AuditCheckCodebaseSizeFiles.
 */

class AuditCheckCodebaseSizeFiles extends AuditCheck {
  /**
   * Implements \AuditCheck\getLabel().
   */
  public function getLabel() {
    return dt('Size of sites/default/files');
  }

  /**
   * Implements \AuditCheck\getDescription().
   */
  public function getDescription() {
    return dt('Determine the size of sites/default/files.');
  }

  /**
   * Implements \AuditCheck\getResultFail().
   */
  public function getResultFail() {
    return dt('Unable to determine size of sites/default/files!');
  }

  /**
   * Implements \AuditCheck\getResultInfo().
   */
  public function getResultInfo() {
    return dt('Size: @size_files_mbMB', array(
      '@size_files_mb' => number_format($this->registry['size_files_mb']),
    ));
  }

  /**
   * Implements \AuditCheck\getResultPass().
   */
  public function getResultPass() {}

  /**
   * Implements \AuditCheck\getResultWarning().
   */
  public function getResultWarning() {}

  /**
   * Implements \AuditCheck\getAction().
   */
  public function getAction() {}

  /**
   * Implements \AuditCheck\getScore().
   */
  public function getScore() {
    $drupal_root = drush_get_context('DRUSH_SELECTED_DRUPAL_ROOT');
    exec('du -s -k -x ' . $drupal_root . '/sites/default/files', $result);
    $kb_size_files = trim($result[0]);
    $this->registry['size_files_mb'] = round($kb_size_files / 1024, 2);
    if (!$this->registry['size_files_mb']) {
      $this->abort = TRUE;
      return AuditCheck::AUDIT_CHECK_SCORE_FAIL;
    }
    return AuditCheck::AUDIT_CHECK_SCORE_INFO;
  }
}
