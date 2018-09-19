<?php
/**
 * @file
 * Contains Drupal\site_audit\Plugin\SiteAuditCheck\DatabaseSize
 */

namespace Drupal\site_audit\Plugin\SiteAuditCheck;

use Drupal\site_audit\Plugin\SiteAuditCheckBase;

/**
 * Provides the CronLast Check.
 *
 * @SiteAuditCheck(
 *  id = "database_size",
 *  name = @Translation("Total size"),
 *  description = @Translation("Determine the size of the database."),
 *  report = "database",
 *  weight = -1,
 * )
 */
class DatabaseSize extends SiteAuditCheckBase {

  /**
   * {@inheritdoc}.
   */
  public function getResultFail() {
    return $this->t('Empty, or unable to determine the size due to a permission error.');
  }

  /**
   * {@inheritdoc}.
   */
  public function getResultInfo() {
    return $this->t('Total size: @size_in_mbMB', array(
      '@size_in_mb' => number_format($this->registry->table_size / 1048576, 2),
    ));
  }

  /**
   * {@inheritdoc}.
   */
  public function getResultPass() {}

  /**
   * {@inheritdoc}.
   */
  public function getResultWarn() {}

  /**
   * {@inheritdoc}.
   */
  public function getAction() {}

  /**
   * {@inheritdoc}.
   */
  public function calculateScore() {
    $connection = \Drupal\Core\Database\Database::getConnection();
    try {
      $query = db_select('information_schema.TABLES', 'ist');
      $query->addExpression('SUM(ist.data_length + ist.index_length)');
      $query->condition('ist.table_schema', $connection->getConnectionOptions()['database']);
      $query->groupBy('ist.table_schema');
      $this->registry->table_size = $query->execute()->fetchField();
      if (!$this->registry->table_size) {
        $this->abort = TRUE;
        return SiteAuditCheckBase::AUDIT_CHECK_SCORE_FAIL;
      }
      return SiteAuditCheckBase::AUDIT_CHECK_SCORE_INFO;
    }
    catch (Exception $e) {
      return SiteAuditCheckBase::AUDIT_CHECK_SCORE_FAIL;
    }
  }

}