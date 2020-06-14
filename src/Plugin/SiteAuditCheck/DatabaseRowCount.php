<?php

namespace Drupal\site_audit\Plugin\SiteAuditCheck;

use Drupal\Core\Database\Database;
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
    $table_rows = [];
    foreach ($this->registry->rows_by_table as $table_name => $rows) {
      $table_rows[] = [
        $table_name,
        $rows,
      ];
    }

    $header = [
      $this->t('Table Name'),
      $this->t('Rows'),
    ];
    return [
      '#theme' => 'table',
      '#class' => 'table-condensed',
      '#header' => $header,
      '#rows' => $table_rows,
    ];
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
    $connection = Database::getConnection();
    $this->registry->rows_by_table = [];
    $warning = FALSE;
    $query = \Drupal::database()->select('information_schema.TABLES', 'ist');
    $query->fields('ist', ['TABLE_NAME', 'TABLE_ROWS']);
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
