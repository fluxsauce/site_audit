<?php

namespace Drupal\site_audit\Plugin\SiteAuditCheck;

use Drupal\site_audit\Plugin\SiteAuditCheckBase;

/**
 * Provides the UsersCountBlocked Check.
 *
 * @SiteAuditCheck(
 *  id = "users_count_blocked",
 *  name = @Translation("Count Blocked"),
 *  description = @Translation("Total number of blocked Drupal users."),
 *  report = "users"
 * )
 */
class UsersCountBlocked extends SiteAuditCheckBase {

  /**
   * {@inheritdoc}.
   */
  public function getResultFail() {}

  /**
   * {@inheritdoc}.
   */
  public function getResultInfo() {
    switch ($this->registry->count_users_blocked) {
      case 0:
        return $this->t('There are no blocked users.');

      break;
      default:
        return $this->formatPlural($this->registry->count_users_blocked, 'There is one blocked user.', 'There are @count blocked users.');
      break;
    }
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
    $query = \Drupal::database()->select('users_field_data', 'ufd');
    $query->addExpression('COUNT(*)', 'count');
    $query->condition('status', 0);

    $this->registry->count_users_blocked = $query->execute()->fetchField();
    return SiteAuditCheckBase::AUDIT_CHECK_SCORE_INFO;
  }

}
