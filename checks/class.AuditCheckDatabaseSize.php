<?php
/**
 * @file
 * Contains \AuditCheckDatabaseSize.
 */

class AuditCheckDatabaseSize extends AuditCheck {
  /**
   * Implements \AuditCheck\getLabel().
   */
  public function getLabel() {
    return dt('Total size');
  }

  /**
   * Implements \AuditCheck\getDescription().
   */
  public function getDescription() {
    return dt('Determine the size of the database.');
  }

  /**
   * Implements \AuditCheck\getResultFail().
   */
  public function getResultFail() {
    return dt('Empty!');
  }

  /**
   * Implements \AuditCheck\getResultInfo().
   */
  public function getResultInfo() {
    return dt('Total size: @size_in_mbMB', array(
      '@size_in_mb' => number_format(round($this->registry['rows_by_table'] / 1048576, 2)),
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
   * Implements \AuditCheck\calculateScore().
   */
  public function calculateScore() {
    $db_spec = _drush_sql_get_db_spec();
    $sql_query  = 'SELECT SUM(TABLES.data_length + TABLES.index_length) ';
    $sql_query .= 'FROM information_schema.TABLES ';
    $sql_query .= 'WHERE TABLES.table_schema = :dbname ';
    $sql_query .= 'GROUP BY TABLES.table_schema ';
    $this->registry['rows_by_table'] = db_query($sql_query, array(
      ':dbname' => $db_spec['database'],
    ))->fetchField();
    if (!$this->registry['rows_by_table']) {
      $this->abort = TRUE;
      return AuditCheck::AUDIT_CHECK_SCORE_FAIL;
    }
    return AuditCheck::AUDIT_CHECK_SCORE_INFO;
  }
}
