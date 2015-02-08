<?php
/**
 * @file
 * Contains \SiteAudit\Check\Database\Size.
 */

class SiteAuditCheckDatabaseSize extends SiteAuditCheckAbstract {
  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Total size');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Determine the size of the database.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {
    return dt('Empty, or unable to determine the size due to a permission error.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultInfo().
   */
  public function getResultInfo() {
    return dt('Total size: @size_in_mbMB', array(
      '@size_in_mb' => number_format($this->registry['rows_by_table'] / 1048576, 2),
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
    if (version_compare(DRUSH_VERSION, 7, '>=')) {
      $sql = drush_sql_get_class();
      $db_spec = $sql->db_spec();
    }
    else {
      $db_spec = _drush_sql_get_db_spec();
    }

    try {
      $sql_query = 'SELECT SUM(TABLES.data_length + TABLES.index_length) ';
      $sql_query .= 'FROM information_schema.TABLES ';
      $sql_query .= 'WHERE TABLES.table_schema = :dbname ';
      $sql_query .= 'GROUP BY TABLES.table_schema ';
      $this->registry['rows_by_table'] = db_query($sql_query, array(
        ':dbname' => $db_spec['database'],
      ))->fetchField();
      if (!$this->registry['rows_by_table']) {
        $this->abort = TRUE;
        return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL;
      }
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO;
    }
    catch (PDOException $e) {
      // Error executing the query.
      $this->abort = TRUE;
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL;
    }

    // Empty database.
    $this->abort = TRUE;
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL;
  }
}
