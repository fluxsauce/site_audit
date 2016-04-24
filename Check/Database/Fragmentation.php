<?php
/**
 * @file
 * Contains \SiteAudit\Check\Database\Fragmentation.
 */

/**
 * Class SiteAuditCheckDatabaseFragmentation.
 */
class SiteAuditCheckDatabaseFragmentation extends SiteAuditCheckAbstract {

  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Database Fragmentation');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Detect table fragmentation which increases storage space and decreases I/O efficiency.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getResultInfo().
   */
  public function getResultInfo() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getResultPass().
   */
  public function getResultPass() {
    return dt('None of the tables has fragmentation ration greater than 5.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultWarn().
   */
  public function getResultWarn() {
    arsort($this->registry['database_fragmentation']);
    if (drush_get_option('html')) {
      $ret_val = '<table class="table table-condensed">';
      $ret_val .= '<thead><tr><th>' . dt('Table Name') . '</th><th>' . dt('Fragmentation Ratio') . '</th></tr></thead>';
      $ret_val .= '<tbody>';
      foreach ($this->registry['database_fragmentation'] as $name => $ratio) {
        $ret_val .= '<tr>';
        $ret_val .= '<td>' . $name . '</td>';
        $ret_val .= '<td>' . $ratio . '</td>';
        $ret_val .= '</tr>';
      }
      $ret_val .= '</tbody>';
      $ret_val .= '</table>';
    }
    else {
      $ret_val  = dt('Table Name: Fragmentation Ratio') . PHP_EOL;
      if (!drush_get_option('json')) {
        $ret_val .= str_repeat(' ', 4);
      }
      $ret_val .= '---------------------';
      foreach ($this->registry['database_fragmentation'] as $name => $ratio) {
        $ret_val .= PHP_EOL;
        if (!drush_get_option('json')) {
          $ret_val .= str_repeat(' ', 4);
        }
        $ret_val .= "$name: $ratio";
      }
    }
    return $ret_val;
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getAction().
   */
  public function getAction() {
    if ($this->getScore() == SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN) {
      return dt('Run "OPTIMIZE TABLE" on the fragmented tables. Refer to https://dev.mysql.com/doc/en/optimize-table.html for more details.');
    }
  }

  /**
   * Implements \SiteAudit\Check\Abstract\calculateScore().
   */
  public function calculateScore() {
    if (version_compare(DRUSH_VERSION, 7, '>=')) {
      $sql = drush_sql_get_class();
      $db_spec = $sql->db_spec();
    }
    else {
      $db_spec = _drush_sql_get_db_spec();
    }
    $sql_query  = 'SELECT TABLE_NAME AS name';
    $sql_query .= ', ROUND(DATA_LENGTH / 1024 / 1024) AS data_length';
    $sql_query .= ', ROUND(INDEX_LENGTH / 1024 / 1024) AS index_length';
    $sql_query .= ', ROUND(DATA_FREE / 1024 / 1024) AS data_free';
    $sql_query .= ' FROM information_schema.TABLES ';
    $sql_query .= ' WHERE TABLES.DATA_FREE > 0 ';
    $sql_query .= ' AND TABLES.table_schema = :dbname ';
    $result = db_query($sql_query, array(
      ':dbname' => $db_spec['database'],
    ));
    foreach ($result as $row) {
      $data = $row->data_length + $row->index_length;
      if ($data != 0) {
        $free = $row->data_free;
        $fragmentation_ratio = $free / $data;
        if ($fragmentation_ratio > 0.05) {
          $this->registry['database_fragmentation'][$row->name] = $fragmentation_ratio;
        }
      }
    }
    if (empty($this->registry['database_fragmentation'])) {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS;
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN;
  }

}
