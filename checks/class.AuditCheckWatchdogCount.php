<?php
/**
 * @file
 * Contains \AuditCheckWatchdogCount.
 */

class AuditCheckWatchdogCount extends AuditCheck {
  /**
   * Implements \AuditCheck\getLabel().
   */
  public function getLabel() {
    return dt('Count');
  }

  /**
   * Implements \AuditCheck\getDescription().
   */
  public function getDescription() {
    return dt('Number of dblog entries.');
  }

  /**
   * Implements \AuditCheck\getResultFail().
   */
  public function getResultFail() {}

  /**
   * Implements \AuditCheck\getResultInfo().
   */
  public function getResultInfo() {
    if (!$this->registry['count_entries']) {
      return dt('There are no dblog entries.');
    }
    return dt('There are @count_entries log entries.', array(
      '@count_entries' => $this->registry['count_entries'],
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
    $sql_query  = 'SELECT COUNT(wid) ';
    $sql_query .= 'FROM {watchdog} ';
    $this->registry['count_entries'] = db_query($sql_query)->fetchField();
    if (!$this->registry['count_entries']) {
      $this->abort = TRUE;
    }
    return AuditCheck::AUDIT_CHECK_SCORE_INFO;
  }
}
