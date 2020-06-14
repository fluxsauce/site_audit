<?php

namespace Drupal\site_audit\Plugin\SiteAuditCheck;

use Drupal\site_audit\Plugin\SiteAuditCheckBase;
use Drupal\user\Entity\User;

/**
 * Provides the UsersWhoIsNumberOne Check.
 *
 * @SiteAuditCheck(
 *  id = "users_who_is_number_one",
 *  name = @Translation("Identify UID #1"),
 *  description = @Translation("Show username and email of UID #1."),
 *  report = "users",
 *  weight = -1,
 * )
 */
class UsersWhoIsNumberOne extends SiteAuditCheckBase {

  /**
   * {@inheritdoc}.
   */
  public function getResultFail() {
    return $this->t('UID #1 does not exist! This is a serious problem.');
  }

  /**
   * {@inheritdoc}.
   */
  public function getResultInfo() {
    return $this->t('UID #1: @name, email: @mail', [
      '@name' => $this->registry->uid_1->get('name')->value,
      '@mail' => $this->registry->uid_1->get('mail')->value,
    ]);
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
    $uid_1 = User::load(1);
    if (!$uid_1) {
      $this->abort = TRUE;
      return SiteAuditCheckBase::AUDIT_CHECK_SCORE_FAIL;
    }
    $this->registry->uid_1 = $uid_1;
    return SiteAuditCheckBase::AUDIT_CHECK_SCORE_INFO;
  }

}
