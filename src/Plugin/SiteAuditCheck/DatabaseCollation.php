<?php
/**
 * @file
 * Contains Drupal\site_audit\Plugin\SiteAuditCheck\DatabaseCollation
 */

namespace Drupal\site_audit\Plugin\SiteAuditCheck;

use Drupal\site_audit\Plugin\SiteAuditCheckBase;

/**
 * Provides the CronLast Check.
 *
 * @SiteAuditCheck(
 *  id = "database_collation",
 *  name = @Translation("Collations"),
 *  description = @Translation("Check to see if there are any tables that aren't using UTF-8."),
 *  report = "database"
 * )
 */
class DatabaseCollation extends SiteAuditCheckBase {

  /**
   * {@inheritdoc}.
   */
  public function getResultFail() {}

  /**
   * {@inheritdoc}.
   */
  public function getResultInfo() {
    //if (drush_get_option('html')) {
    if (TRUE) {
      $ret_val = '<table class="table table-condensed">';
      $ret_val .= '<thead><tr><th>' . $this->t('Table Name') . '</th><th>' . $this->t('Collation') . '</th></tr></thead>';
      $ret_val .= '<tbody>';
      foreach ($this->registry->collation_tables as $name => $collation) {
        $ret_val .= '<tr>';
        $ret_val .= '<td>' . $name . '</td>';
        $ret_val .= '<td>' . $collation . '</td>';
        $ret_val .= '</tr>';
      }
      $ret_val .= '</tbody>';
      $ret_val .= '</table>';
    }
    else {
      $ret_val  = dt('Table Name: Collation') . PHP_EOL;
      if (!drush_get_option('json')) {
        $ret_val .= str_repeat(' ', 4);
      }
      $ret_val .= '---------------------';
      foreach ($this->registry->collation_tables as $name => $collation) {
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
   * {@inheritdoc}.
   */
  public function getResultPass() {
    return $this->t('Every table is using UTF-8.');
  }

  /**
   * {@inheritdoc}.
   */
  public function getResultWarn() {
    return $this->getResultInfo();
  }

  /**
   * {@inheritdoc}.
   */
  public function getAction() {
     if ($this->getScore() == SiteAuditCheckBase::AUDIT_CHECK_SCORE_WARN) {
      return $this->t('In MySQL, use the command "!command" to convert the affected tables. Of course, test first and ensure your data will not be negatively affected.', [
        '!command' => 'ALTER TABLE table_name CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;',
      ]);
    }
  }

  /**
   * {@inheritdoc}.
   */
  public function calculateScore() {
    $connection = \Drupal\Core\Database\Database::getConnection();
    $query = \Drupal::database()->select('information_schema.TABLES', 'ist');
    $query->addField('ist', 'TABLE_NAME', 'name');
    $query->addField('ist', 'TABLE_COLLATION', 'collation');
    $query->condition('ist.TABLE_COLLATION', ['utf8_general_ci', 'utf8_unicode_ci', 'utf8_bin', 'utf8mb4_general_ci'], 'NOT IN');
    $query->condition('ist.table_schema', $connection->getConnectionOptions()['database']);
    $result = $query->execute();
    $count = 0;
    $warn = FALSE;
    while ($row = $result->fetchAssoc()) {
      $count++;
      $this->registry->collation_tables[$row['name']] = $row['collation'];
      // Special case for old imports.
      if ($row['collation'] == 'latin1_swedish_ci') {
        $warn = TRUE;
      }
    }

    if ($count === 0) {
      return SiteAuditCheckBase::AUDIT_CHECK_SCORE_PASS;
    }
    if ($warn) {
      return SiteAuditCheckBase::AUDIT_CHECK_SCORE_WARN;
    }
    return SiteAuditCheckBase::AUDIT_CHECK_SCORE_INFO;
  }

}
