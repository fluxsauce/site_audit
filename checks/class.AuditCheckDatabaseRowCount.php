<?php

class AuditCheckDatabaseRowCount extends AuditCheck {
  const AUDIT_CHECK_DB_ROW_MIN_DEFAULT = 1000;
  protected $_rows_by_table;

  public function getLabel() {
    return dt('Tables with at least @min_rows rows', array(
      '@min_rows' => drush_get_option('min_rows', AuditCheckDatabaseRowCount::AUDIT_CHECK_DB_ROW_MIN_DEFAULT),
    ));
  }

  public function getResultFail() {}

  public function getResultInfo() {
    if (empty($this->_rows_by_table)) {
      return dt('No tables with less than @min_rows rows.', array(
        '@min_rows' => drush_get_option('min_rows', AuditCheckDatabaseRowCount::AUDIT_CHECK_DB_ROW_MIN_DEFAULT),
      ));
    }
    if (drush_get_option('html')) {
      $this->html = TRUE;
      $ret_val = '<table>';
      $ret_val .= '<thead><tr><th>Table Name</th><th>Rows</th></tr></thead>';
      $ret_val .= '<tbody>';
      foreach ($this->_rows_by_table as $table_name => $rows) {
        $ret_val .= '<tr>';
        $ret_val .= '<td>' . $table_name . '</td>';
        $ret_val .= '<td>' . $rows . '</td>';
        $ret_val .= '</tr>';
      }
      $ret_val .= '</tbody>';
    }
    else {
      $ret_val  = 'Table Name: Rows' . PHP_EOL;
      $ret_val .= '----------------' . PHP_EOL;
      foreach ($this->_rows_by_table as $table_name => $rows) {
        $ret_val .= "$table_name: $rows" . PHP_EOL;
      }
    }
    return $ret_val;
  }

  public function getResultPass() {}

  public function getResultWarning() {
    return $this->getResultInfo();
  }

  public function getAction() {}

  public function getDescription() {
    return dt('Return list of all tables with at least @min_rows rows in the database.', array(
      '@min_rows' => drush_get_option('min_rows', AuditCheckDatabaseRowCount::AUDIT_CHECK_DB_ROW_MIN_DEFAULT),
    ));
  }

  public function getScore() {
    $warning = FALSE;
    $db_spec = _drush_sql_get_db_spec();
    $sql_query  = 'SELECT TABLE_NAME AS table_name, TABLE_ROWS AS rows ';
    $sql_query .= 'FROM information_schema.TABLES ';
    $sql_query .= 'WHERE TABLES.TABLE_SCHEMA = :dbname ';
    $sql_query .= 'AND TABLE_ROWS >= :count ';
    $sql_query .= 'ORDER BY TABLE_ROWS desc ';
    $result = db_query($sql_query, array(
      ':count' => drush_get_option('min_rows', AuditCheckDatabaseRowCount::AUDIT_CHECK_DB_ROW_MIN_DEFAULT),
      ':dbname' => $db_spec['database'],
    ));
    foreach ($result as $row) {
      if ($row->rows > drush_get_option('min_rows', AuditCheckDatabaseRowCount::AUDIT_CHECK_DB_ROW_MIN_DEFAULT)) {
        $warning = TRUE;
      }
      $this->rows_by_table[$row->table_name] = $row->rows;
    }
    if ($warning) {
      return AuditCheck::AUDIT_CHECK_SCORE_WARN;
    }
    return AuditCheck::AUDIT_CHECK_SCORE_INFO;
  }
}