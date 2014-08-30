<?php
/**
 * @file
 * Contains \SiteAudit\Check\Codebase\ManagedFileSize.
 */

class SiteAuditCheckCodebaseManagedFileSize extends SiteAuditCheckAbstract {
  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Drupal managed file size');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Determine the size of Drupal managed files.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getResultInfo().
   */
  public function getResultInfo() {
    if ($this->registry['managed_filesize'] < 1048576) {
      return dt('Managed file size: @managed_filesize_kbkB', array(
        '@managed_filesize_kb' => number_format($this->registry['managed_filesize'] / 1024, 2),
      ));
    }
    return dt('Managed file size: @managed_filesize_mbMB', array(
      '@managed_filesize_mb' => number_format($this->registry['managed_filesize'] / 1048576, 2),
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
    if (!$this->registry['managed_file_count']) {
      $this->registry['managed_filesize'] = 0;
    }
    else {
      $sql_query  = 'SELECT SUM(filesize) ';
      $sql_query .= 'FROM {file_managed} ';
      $sql_query .= 'WHERE status = 1 ';
      $this->registry['managed_filesize'] = db_query($sql_query)->fetchField();
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO;
  }
}
