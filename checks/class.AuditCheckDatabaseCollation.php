<?php

class AuditCheckDatabaseCollation extends AuditCheck {
  const AUDIT_CHECK_DB_COLLATION_DEFAULT = 'utf8_general_ci';
  protected $_collation_tables;

  public function getLabel() {
    return dt('Collations');
  }

  public function getResultFail() {}

  public function getResultInfo() {
    if (drush_get_option('html')) {
      $this->html = TRUE;
      $ret_val = '<table>';
      $ret_val .= '<thead><tr><th>Table Name</th><th>Collation</th></tr></thead>';
      $ret_val .= '<tbody>';
      foreach ($this->_collation_tables as $name => $collation) {
        $ret_val .= '<tr>';
        $ret_val .= '<td>' . $name . '</td>';
        $ret_val .= '<td>' . $collation . '</td>';
        $ret_val .= '</tr>';
      }
      $ret_val .= '</tbody>';
    }
    else {
      $ret_val  = 'Table Name: Collation' . PHP_EOL;
      $ret_val .= '---------------------' . PHP_EOL;
      foreach ($this->_collation_tables as $name => $collation) {
        $ret_val .= "$name: $collation" . PHP_EOL;
      }
    }
    return $ret_val;
  }

  public function getResultPass() {
    return dt('Every table is using @collation.', array(
      '@collation' => drush_get_option('expected_collation', self::AUDIT_CHECK_DB_COLLATION_DEFAULT),
    ));
  }

  public function getResultWarning() {
    return $this->getResultInfo();
  }

  public function getAction() {}

  public function getDescription() {
    return dt('Check to see if there are any tables that aren\'t using @collation.', array(
      '@collation' => drush_get_option('expected_collation', self::AUDIT_CHECK_DB_COLLATION_DEFAULT),
    ));
  }

  public function getScore() {
    $db_spec = _drush_sql_get_db_spec();
    $sql_query  = 'SELECT TABLE_NAME AS name ';
    $sql_query .= ', TABLE_COLLATION AS collation ';
    $sql_query .= 'FROM information_schema.TABLES ';
    $sql_query .= 'WHERE TABLES.table_schema = :dbname ';
    $sql_query .= 'AND TABLE_COLLATION != :collation ';
    $result = db_query($sql_query, array(
      ':dbname' => $db_spec['database'],
      ':collation' => drush_get_option('expected_collation', self::AUDIT_CHECK_DB_COLLATION_DEFAULT),
    ));
    if (!$result->rowCount()) {
      return AuditCheck::AUDIT_CHECK_SCORE_PASS;
    }
    $warn = FALSE;
    foreach ($result as $row) {
      $this->_collation_tables[$row->name] = $row->collation;
      // Special case for old imports.
      if ($row->collation == 'latin1_swedish_ci') {
        $warn = TRUE;
      }
    }
    if ($warn) {
      return AuditCheck::AUDIT_CHECK_SCORE_WARN;
    }
    return AuditCheck::AUDIT_CHECK_SCORE_INFO;
  }
}
