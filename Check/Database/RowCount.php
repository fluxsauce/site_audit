<?php
/**
 * @file
 * Contains \SiteAudit\Check\Database\RowCount.
 */

class SiteAuditCheckDatabaseRowCount extends SiteAuditCheckAbstract {
  const AUDIT_CHECK_DB_ROW_MIN_DEFAULT = 1000;

  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Tables with at least @min_rows rows', array(
      '@min_rows' => drush_get_option('min_rows', SiteAuditCheckDatabaseRowCount::AUDIT_CHECK_DB_ROW_MIN_DEFAULT),
    ));
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Return list of all tables with at least @min_rows rows in the database.', array(
      '@min_rows' => drush_get_option('min_rows', SiteAuditCheckDatabaseRowCount::AUDIT_CHECK_DB_ROW_MIN_DEFAULT),
    ));
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getResultInfo().
   */
  public function getResultInfo() {
    if (empty($this->registry['rows_by_table'])) {
      return dt('No tables with less than @min_rows rows.', array(
        '@min_rows' => drush_get_option('min_rows', SiteAuditCheckDatabaseRowCount::AUDIT_CHECK_DB_ROW_MIN_DEFAULT),
      ));
    }
    if (drush_get_option('html')) {
      $ret_val = '<table class="table table-condensed">';
      $ret_val .= '<thead><tr><th>Table Name</th><th>Rows</th></tr></thead>';
      $ret_val .= '<tbody>';
      foreach ($this->registry['rows_by_table'] as $table_name => $rows) {
        $ret_val .= '<tr>';
        $ret_val .= '<td>' . $table_name . '</td>';
        $ret_val .= '<td>' . $rows . '</td>';
        $ret_val .= '</tr>';
      }
      $ret_val .= '</tbody>';
      $ret_val .= '</table>';
    }
    else {
      $ret_val  = str_repeat(' ', 2) . dt('Table Name: Rows') . PHP_EOL;
      $ret_val .= str_repeat(' ', 4) . '----------------' . PHP_EOL;
      foreach ($this->registry['rows_by_table'] as $table_name => $rows) {
        $ret_val .= str_repeat(' ', 4) . "$table_name: $rows" . PHP_EOL;
      }
    }
    return $ret_val;
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultPass().
   */
  public function getResultPass() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getResultWarn().
   */
  public function getResultWarn() {
    return $this->getResultInfo();
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getAction().
   */
  public function getAction() {}

  /**
   * Implements \SiteAudit\Check\Abstract\calculateScore().
   */
  public function calculateScore() {
    $this->registry['rows_by_table'] = array();
    $warning = FALSE;
    $db_spec = _drush_sql_get_db_spec();
    $sql_query  = 'SELECT TABLE_NAME AS table_name, TABLE_ROWS AS rows ';
    $sql_query .= 'FROM information_schema.TABLES ';
    $sql_query .= 'WHERE TABLES.TABLE_SCHEMA = :dbname ';
    $sql_query .= 'AND TABLE_ROWS >= :count ';
    $sql_query .= 'ORDER BY TABLE_ROWS desc ';
    $result = db_query($sql_query, array(
      ':count' => drush_get_option('min_rows', SiteAuditCheckDatabaseRowCount::AUDIT_CHECK_DB_ROW_MIN_DEFAULT),
      ':dbname' => $db_spec['database'],
    ));
    foreach ($result as $row) {
      if ($row->rows > drush_get_option('min_rows', SiteAuditCheckDatabaseRowCount::AUDIT_CHECK_DB_ROW_MIN_DEFAULT)) {
        $warning = TRUE;
      }
      $this->registry['rows_by_table'][$row->table_name] = $row->rows;
    }
    if ($warning) {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN;
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO;
  }
}
