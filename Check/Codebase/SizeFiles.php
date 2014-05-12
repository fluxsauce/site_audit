<?php
/**
 * @file
 * Contains \SiteAudit\Check\Codebase\SizeFiles.
 */

class SiteAuditCheckCodebaseSizeFiles extends SiteAuditCheckAbstract {
  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Size of @files_folder', array('@files_folder' => variable_get('file_public_path', conf_path() . '/files')));
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Determine the size of @files_folder.', array('@files_folder' => variable_get('file_public_path', conf_path() . '/files')));
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {
    return dt('Unable to determine size of @files_folder!', array('@files_folder' => variable_get('file_public_path', conf_path() . '/files')));
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultInfo().
   */
  public function getResultInfo() {
    if ($this->registry['size_files_kb'] < 1024) {
      return dt('Files: @size_files_kbkB', array(
        '@size_files_kb' => number_format($this->registry['size_files_kb']),
      ));
    }
    return dt('Files: @size_files_mbMB', array(
      '@size_files_mb' => number_format($this->registry['size_files_kb'] / 1024, 2),
    ));
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
    $drupal_root = drush_get_context('DRUSH_SELECTED_DRUPAL_ROOT');
    exec('du -s -k -x ' . $drupal_root . '/' . variable_get('file_public_path', conf_path() . '/files') . '/', $result);
    $size_files_kb_exploded = explode("\t", trim($result[0]));
    $this->registry['size_files_kb'] = $size_files_kb_exploded[0];
    if (!$this->registry['size_files_kb']) {
      $this->abort = TRUE;
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL;
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO;
  }
}
