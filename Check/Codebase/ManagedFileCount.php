<?php
/**
 * @file
 * Contains \SiteAudit\Check\Codebase\ManagedFileCount.
 */

class SiteAuditCheckCodebaseManagedFileCount extends SiteAuditCheckAbstract {
  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Drupal managed file count');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Determine the count of Drupal managed files.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getResultInfo().
   */
  public function getResultInfo() {
    return dt('Managed file count: @count', array(
      '@count' => number_format($this->registry['managed_file_count']),
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
    $sql_query  = 'SELECT COUNT(fid) ';
    $sql_query .= 'FROM {file_managed} ';
    $sql_query .= 'WHERE status = 1 ';
    $this->registry['managed_file_count'] = db_query($sql_query)->fetchField();
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO;
  }
}
