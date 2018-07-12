<?php
/**
 * @file
 * Contains Drupal\site_audit\Plugin\SiteAuditCheck\DatabaseRowCount
 */

namespace Drupal\site_audit\Plugin\SiteAuditCheck;

use Drupal\site_audit\Plugin\SiteAuditCheckBase;

/**
 * Provides the CronLast Check.
 *
 * @SiteAuditCheck(
 *  id = "database_row_count",
 *  name = @Translation("Tables with at least 1000 rows"),
 *  description = @Translation("Return list of all tables with at least 1000 rows in the database."),
 *  report = "database"
 * )
 */
class DatabaseRowCount extends SiteAuditCheckBase {

  /**
   * {@inheritdoc}.
   */
  public function getResultFail() {}

  /**
   * {@inheritdoc}.
   */
  public function getResultInfo() {
    if (empty($this->registry->rows_by_table)) {
      return $this->t('No tables with more than 1000 rows.');
    }
    //if (drush_get_option('html')) {
    if (TRUE) {
      $ret_val = '<table class="table table-condensed">';
      $ret_val .= '<thead><tr><th>'. $this->t('Table Name') . '</th><th>' . $this->t('Rows') . '</th></tr></thead>';
      $ret_val .= '<tbody>';
      foreach ($this->registry->rows_by_table as $table_name => $rows) {
        $ret_val .= '<tr>';
        $ret_val .= '<td>' . $table_name . '</td>';
        $ret_val .= '<td>' . $rows . '</td>';
        $ret_val .= '</tr>';
      }
      $ret_val .= '</tbody>';
      $ret_val .= '</table>';
    }
    else {
      $ret_val = $this->t('Table Name: Rows') . PHP_EOL;
      if (!drush_get_option('json')) {
        $ret_val .= str_repeat(' ', 4);
      }
      $ret_val .= '----------------';
      foreach ($this->registry->rows_by_table as $table_name => $rows) {
        $ret_val .= PHP_EOL;
        if (!drush_get_option('json')) {
          $ret_val .= str_repeat(' ', 4);
        }
        $ret_val .= "$table_name: $rows";
      }
    }
    return $ret_val;
  }

  /**
   * {@inheritdoc}.
   */
  public function getResultPass() {}

  /**
   * {@inheritdoc}.
   */
  public function getResultWarn() {
    return $this->getResultInfo();
  }

  /**
   * {@inheritdoc}.
   */
  public function getAction() {}

  /**
   * {@inheritdoc}.
   */
  public function calculateScore() {
    $connection = \Drupal\Core\Database\Database::getConnection();
    $this->registry->rows_by_table = array();
    $warning = FALSE;
    $query = db_select('information_schema.TABLES', 'ist');
    $query->fields('ist', array('TABLE_NAME', 'TABLE_ROWS'));
    $query->condition('ist.TABLE_ROWS', 1000, '>');
    $query->condition('ist.table_schema', $connection->getConnectionOptions()['database']);
    $query->orderBy('TABLE_ROWS', 'DESC');
    $result = $query->execute()->fetchAllKeyed();
    foreach ($result as $table => $rows) {
      if ($rows > 1000) {
        $warning = TRUE;
      }
      $this->registry->rows_by_table[$table] = $rows;
    }
    if ($warning) {
      return SiteAuditCheckBase::AUDIT_CHECK_SCORE_WARN;
    }
    return SiteAuditCheckBase::AUDIT_CHECK_SCORE_INFO;
  }

}