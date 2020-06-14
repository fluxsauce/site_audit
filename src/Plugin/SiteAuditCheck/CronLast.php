<?php

namespace Drupal\site_audit\Plugin\SiteAuditCheck;

use Drupal\site_audit\Plugin\SiteAuditCheckBase;

/**
 * Provides the CronLast Check.
 *
 * @SiteAuditCheck(
 *  id = "cron_last",
 *  name = @Translation("Last run"),
 *  description = @Translation("Time Cron last executed"),
 *  report = "cron"
 * )
 */
class CronLast extends SiteAuditCheckBase {

  /**
   * {@inheritdoc}.
   */
  public function getResultFail() {}

  /**
   * {@inheritdoc}.
   */
  public function getResultInfo() {
    if ($this->registry->cron_last) {
      return $this->t('Cron last ran at @date (@ago ago)', [
        '@date' => date('r', $this->registry->cron_last),
        '@ago' => \Drupal::service('date.formatter')->formatInterval(time() - $this->registry->cron_last),
      ]);
    }
    return $this->t('Cron has never run.');
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
    return SiteAuditCheckBase::AUDIT_CHECK_SCORE_INFO;
  }

}
