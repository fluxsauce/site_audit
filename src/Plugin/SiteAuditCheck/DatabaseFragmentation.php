<?php

namespace Drupal\site_audit\Plugin\SiteAuditCheck;

use Drupal\Core\Database\Database;
use Drupal\site_audit\Plugin\SiteAuditCheckBase;

/**
 * Provides the CronLast Check.
 *
 * @SiteAuditCheck(
 *  id = "database_fragmentation",
 *  name = @Translation("Database Fragmentation"),
 *  description = @Translation("Detect table fragmentation which increases storage space and decreases I/O efficiency."),
 *  report = "database"
 * )
 */
class DatabaseFragmentation extends SiteAuditCheckBase {

  /**
   * {@inheritdoc}.
   */
  public function getResultFail() {}

  /**
   * {@inheritdoc}.
   */
  public function getResultInfo() {}

  /**
   * {@inheritdoc}.
   */
  public function getResultPass() {}

  /**
   * {@inheritdoc}.
   */
  public function getResultWarn() {
    arsort($this->registry->database_fragmentation);
    $header = [
      'table_name' => $this->t('Table Name'),
      'frag_ratio' => $this->t('Fragmentation Ratio'),
    ];
    $rows = [];
    foreach ($this->registry->database_fragmentation as $name => $ratio) {
      $rows[] = [
        'table_name' => $name,
        'frag_ratio' => $ratio,
      ];
    }
    return [
      '#theme' => 'table',
      '#header' => $header,
      '#rows' => $rows,
    ];
  }

  /**
   * {@inheritdoc}.
   */
  public function getAction() {
    if ($this->getScore() == SiteAuditCheckBase::AUDIT_CHECK_SCORE_WARN) {
      return $this->t('Run "OPTIMIZE TABLE" on the fragmented tables. Refer to https://dev.mysql.com/doc/en/optimize-table.html for more details.');
    }
  }

  /**
   * {@inheritdoc}.
   */
  public function calculateScore() {
    $connection = Database::getConnection();
    $query = \Drupal::database()->select('information_schema.TABLES', 'ist');
    $query->fields('ist', ['TABLE_NAME']);
    $query->addExpression('ROUND(DATA_LENGTH / 1024 / 1024)', 'data_length');
    $query->addExpression('ROUND(INDEX_LENGTH / 1024 / 1024)', 'index_length');
    $query->addExpression('ROUND(DATA_FREE / 1024 / 1024)', 'data_free');
    $query->condition('ist.DATA_FREE', 0, '>');
    $query->condition('ist.table_schema', $connection->getConnectionOptions()['database']);
    $result = $query->execute();
    while ($row = $result->fetchAssoc()) {
      $data = $row['data_length'] + $row['index_length'];
      if ($data != 0) {
        $free = $row['data_free'];
        $fragmentation_ratio = $free / $data;
        if ($fragmentation_ratio > 0.05) {
          $this->registry->database_fragmentation[$row['TABLE_NAME']] = $fragmentation_ratio;
        }
      }
    }
    if (empty($this->registry->database_fragmentation)) {
      return SiteAuditCheckBase::AUDIT_CHECK_SCORE_PASS;
    }
    return SiteAuditCheckBase::AUDIT_CHECK_SCORE_WARN;
  }

}
