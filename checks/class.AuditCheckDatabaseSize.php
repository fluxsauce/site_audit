<?php

class AuditCheckDatabaseSize extends AuditCheck {
  protected $_size_bytes;

  public function getLabel() {
    return dt('Total size');
  }

  public function getResultFail() {
    return dt('Empty!');
  }

  public function getResultInfo() {
    return dt('Total size: @size_in_mbMB', array(
      '@size_in_mb' => number_format(round($this->_size_bytes / 1048576, 2)),
    ));
  }

  public function getResultPass() {}

  public function getResultWarning() {}

  public function getAction() {}

  public function getDescription() {
    return dt('Determine the size of the database.');
  }

  public function getScore() {
    $db_spec = _drush_sql_get_db_spec();
    $sql_query  = 'SELECT SUM(TABLES.data_length + TABLES.index_length) ';
    $sql_query .= 'FROM information_schema.TABLES ';
    $sql_query .= 'WHERE TABLES.table_schema = :dbname ';
    $sql_query .= 'GROUP BY TABLES.table_schema ';
    $this->_size_bytes = db_query($sql_query, array(
      ':dbname' => $db_spec['database'],
    ))->fetchField();
    if (!$this->_size_bytes) {
      $this->abort = TRUE;
      return AuditCheck::AUDIT_CHECK_SCORE_FAIL;
    }
    return AuditCheck::AUDIT_CHECK_SCORE_INFO;
  }
}
