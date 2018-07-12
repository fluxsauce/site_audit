<?php
/**
 * @file
 * Contains Drupal\site_audit\Plugin\SiteAuditCheck\DatabaseEngine
 */

namespace Drupal\site_audit\Plugin\SiteAuditCheck;

use Drupal\site_audit\Plugin\SiteAuditCheckBase;

/**
 * Provides the CronLast Check.
 *
 * @SiteAuditCheck(
 *  id = "database_engine",
 *  name = @Translation("Storage Engines"),
 *  description = @Translation("Check to see if there are any tables that aren\'t using InnoDB."),
 *  report = "database"
 * )
 */
class DatabaseEngine extends SiteAuditCheckBase {

  /**
   * {@inheritdoc}.
   */
  public function getResultFail() {
    //if (drush_get_option('html')) {
    if (TRUE) {
      $ret_val = '<table class="table table-condensed">';
      $ret_val .= '<thead><tr><th>' . $this->t('Table Name') . '</th><th>' . $this->t('Engine') . '</th></tr></thead>';
      $ret_val .= '<tbody>';
      foreach ($this->registry->engine_tables as $name => $engine) {
        $ret_val .= '<tr>';
        $ret_val .= '<td>' . $name . '</td>';
        $ret_val .= '<td>' . $engine . '</td>';
        $ret_val .= '</tr>';
      }
      $ret_val .= '</tbody>';
      $ret_val .= '</table>';
    }
    else {
      $ret_val  = 'Table Name: Engine' . PHP_EOL;
      if (!drush_get_option('json')) {
        $ret_val .= str_repeat(' ', 4);
      }
      $ret_val .= '---------------------';
      foreach ($this->registry->engine_tables as $name => $engine) {
        $ret_val .= PHP_EOL;
        if (!drush_get_option('json')) {
          $ret_val .= str_repeat(' ', 4);
        }
        $ret_val .= "$name: $engine";
      }
    }
    return $ret_val;
  }

  /**
   * {@inheritdoc}.
   */
  public function getResultInfo() {}

  /**
   * {@inheritdoc}.
   */
  public function getResultPass() {
    return $this->t('Every table is using InnoDB.');
  }

  /**
   * {@inheritdoc}.
   */
  public function getResultWarn() {}

  /**
   * {@inheritdoc}.
   */
  public function getAction() {
    if ($this->score != SiteAuditCheckBase::AUDIT_CHECK_SCORE_PASS) {
      return $this->t('Change the Storage Engine to InnoDB. See @url for details.', array(
        '@url' => 'http://dev.mysql.com/doc/refman/5.6/en/converting-tables-to-innodb.html',
      ));
    }
  }

  /**
   * {@inheritdoc}.
   */
  public function calculateScore() {
    $connection = \Drupal\Core\Database\Database::getConnection();
    $query = db_select('information_schema.TABLES', 'ist');
    $query->addField('ist', 'TABLE_NAME', 'name');
    $query->addField('ist', 'ENGINE', 'engine');
    $query->condition('ist.ENGINE', 'InnoDB', '<>');
    $query->condition('ist.table_schema', $connection->getConnectionOptions()['database']);
    $result = $query->execute();
    $count = 0;
    while ($row = $result->fetchAssoc) {
      $count++;
      $this->registry->engine_tables[$row['name']] = $row['engine'];
    }
    if ($count === 0) {
      return SiteAuditCheckBase::AUDIT_CHECK_SCORE_PASS;
    }
    return SiteAuditCheckBase::AUDIT_CHECK_SCORE_FAIL;
  }

}