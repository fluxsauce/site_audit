<?php

namespace Drupal\site_audit\Plugin\SiteAuditCheck;

use Drupal\site_audit\Plugin\SiteAuditCheckBase;

/**
 * Provides the UsersBlockedNumberOne Check.
 *
 * @SiteAuditCheck(
 *  id = "users_blocked_number_one",
 *  name = @Translation("UID #1 access"),
 *  description = @Translation("Determine if UID #1 is blocked."),
 *  report = "users"
 * )
 */
class UsersBlockedNumberOne extends SiteAuditCheckBase {

  /**
   * {@inheritdoc}.
   */
  public function getResultFail() {
    return $this->t('UID #1 is not blocked!');
  }

  /**
   * {@inheritdoc}.
   */
  public function getResultInfo() {}

  /**
   * {@inheritdoc}.
   */
  public function getResultPass() {
    return $this->t('UID #1 is blocked.');
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
      return $this->t('Block UID #1');
    }
  }

  /**
   * {@inheritdoc}.
   */
  public function calculateScore() {
    $query = \Drupal::database()->select('users_field_data', 'ufd');
    $query->addField('ufd', 'status');
    $query->condition('uid', 1);

    if (!$query->execute()->fetchField()) {
      return SiteAuditCheckBase::AUDIT_CHECK_SCORE_PASS;
    }
    return SiteAuditCheckBase::AUDIT_CHECK_SCORE_FAIL;
  }

}
