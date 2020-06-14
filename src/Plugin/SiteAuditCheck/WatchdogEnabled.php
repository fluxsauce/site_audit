<?php

namespace Drupal\site_audit\Plugin\SiteAuditCheck;

use Drupal\site_audit\Plugin\SiteAuditCheckBase;

/**
 * Provides the WatchdogEnabled Check.
 *
 * @SiteAuditCheck(
 *  id = "watchdog_enabled",
 *  name = @Translation("dblog status"),
 *  description = @Translation("Check to see if database logging is enabled."),
 *  report = "watchdog",
 *  weight = -5,
 * )
 */
class WatchdogEnabled extends SiteAuditCheckBase {

  /**
   * {@inheritdoc}.
   */
  public function getResultFail() {}

  /**
   * {@inheritdoc}.
   */
  public function getResultInfo() {
    return $this->t('Database logging (dblog) is not enabled; if the site is having problems, consider enabling it for debugging.');
  }

  /**
   * {@inheritdoc}.
   */
  public function getResultPass() {
    return $this->t('Database logging (dblog) is enabled.');
  }

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
    if (!\Drupal::moduleHandler()->moduleExists('dblog')) {
      $this->registry->watchdog_enabled = FALSE;
      $this->abort = TRUE;
      return SiteAuditCheckBase::AUDIT_CHECK_SCORE_INFO;
    }
    $this->registry->watchdog_enabled = TRUE;
    return SiteAuditCheckBase::AUDIT_CHECK_SCORE_PASS;
  }

}
