<?php
/**
 * @file
 * Contains \SiteAudit\Check\Database\Collation.
 */

class SiteAuditCheckDatabaseCollation extends SiteAuditCheckAbstract {
  const AUDIT_CHECK_DB_COLLATION_DEFAULT = 'utf8_general_ci';

  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Collations');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Check to see if there are any tables that aren\'t using @collation.', array(
      '@collation' => drush_get_option('expected_collation', self::AUDIT_CHECK_DB_COLLATION_DEFAULT),
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
    if (drush_get_option('html')) {
      $ret_val = '<table class="table table-condensed">';
      $ret_val .= '<thead><tr><th>Table Name</th><th>Collation</th></tr></thead>';
      $ret_val .= '<tbody>';
      foreach ($this->registry['collation_tables'] as $name => $collation) {
        $ret_val .= '<tr>';
        $ret_val .= '<td>' . $name . '</td>';
        $ret_val .= '<td>' . $collation . '</td>';
        $ret_val .= '</tr>';
      }
      $ret_val .= '</tbody>';
      $ret_val .= '</table>';
    }
    else {
      $ret_val  = 'Table Name: Collation' . PHP_EOL;
      if (!drush_get_option('json')) {
        $ret_val .= str_repeat(' ', 4);
      }
      $ret_val .= '---------------------';
      foreach ($this->registry['collation_tables'] as $name => $collation) {
        $ret_val .= PHP_EOL;
        if (!drush_get_option('json')) {
          $ret_val .= str_repeat(' ', 4);
        }
        $ret_val .= "$name: $collation";
      }
    }
    return $ret_val;
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultPass().
   */
  public function getResultPass() {
    return dt('Every table is using @collation.', array(
      '@collation' => drush_get_option('expected_collation', self::AUDIT_CHECK_DB_COLLATION_DEFAULT),
    ));
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultWarn().
   */
  public function getResultWarn() {
    return $this->getResultInfo();
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getAction().
   */
  public function getAction() {
    if ($this->getScore() == SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN) {
      return dt('In MySQL, use the command "!command" to convert the affected tables. Of course, test first and ensure your data will not be negatively affected.', array(
        '!command' => 'ALTER TABLE table_name CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;',
      ));
    }
  }

  /**
   * Implements \SiteAudit\Check\Abstract\calculateScore().
   */
  public function calculateScore() {
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
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS;
    }
    $warn = FALSE;
    foreach ($result as $row) {
      $this->registry['collation_tables'][$row->name] = $row->collation;
      // Special case for old imports.
      if ($row->collation == 'latin1_swedish_ci') {
        $warn = TRUE;
      }
    }
    if ($warn) {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN;
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO;
  }
}
