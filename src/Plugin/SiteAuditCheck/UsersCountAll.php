<?php

namespace Drupal\site_audit\Plugin\SiteAuditCheck;

use Drupal\site_audit\Plugin\SiteAuditCheckBase;

/**
 * Provides the UsersCountAll Check.
 *
 * @SiteAuditCheck(
 *  id = "users_count_all",
 *  name = @Translation("Count All"),
 *  description = @Translation("Total number of Drupal users."),
 *  report = "users",
 *  weight = -5,
 * )
 */
class UsersCountAll extends SiteAuditCheckBase {

  /**
   * {@inheritdoc}.
   */
  public function getResultFail() {
    return $this->t('There are no users!');
  }

  /**
   * {@inheritdoc}.
   */
  public function getResultInfo() {
    return $this->formatPlural($this->registry->count_users_all, 'There is one user.', 'There are @count users.');
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
    $query = \Drupal::database()->select('users');
    $query->addExpression('COUNT(*)', 'count');
    $query->condition('uid', 0, '<>');

    try {
      $this->registry->count_users_all = $query->execute()->fetchField();
      if (!$this->registry->count_users_all) {
        $this->abort = TRUE;
        return SiteAuditCheckBase::AUDIT_CHECK_SCORE_FAIL;
      }
      return SiteAuditCheckBase::AUDIT_CHECK_SCORE_INFO;
    }
    catch (DatabaseExceptionWrapper $e) {
      $this->abort = TRUE;
      return SiteAuditCheckBase::AUDIT_CHECK_SCORE_FAIL;
    }
  }

}
