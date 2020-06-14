<?php

namespace Drupal\site_audit\Plugin\SiteAuditCheck;

use Drupal\site_audit\Plugin\SiteAuditCheckBase;

/**
 * Provides the CronEnabled Check.
 *
 * @SiteAuditCheck(
 *  id = "cron_enabled",
 *  name = @Translation("Enabled"),
 *  description = @Translation("Check to see if cron is scheduled to run."),
 *  report = "cron"
 * )
 */
class CronEnabled extends SiteAuditCheckBase {

  /**
   * {@inheritdoc}.
   */
  public function getResultFail() {
    return $this->t('You have disabled cron, which will prevent routine system tasks from executing.');
  }

  /**
   * {@inheritdoc}.
   */
  public function getResultInfo() {}

  /**
   * {@inheritdoc}.
   */
  public function getResultPass() {
    // Manual execution.
    if ($this->registry->cron_safe_threshold === 0) {
      return $this->t('Drupal Cron frequency is set to never, but has been executed within the past 24 hours (either manually or using drush cron).');
    }
    // Default.
    return $this->t('Cron is set to run every @minutes minutes.', [
      '@minutes' => round($this->registry->cron_safe_threshold / 60),
    ]);
  }

  /**
   * {@inheritdoc}.
   */
  public function getResultWarn() {
    if ($this->registry->cron_safe_threshold > (24 * 60 * 60)) {
      return $this->t('Drupal Cron frequency is set to mare than 24 hours.');
    }
    else {
      return $this->t('Drupal Cron has not run in the past day even though it\'s frequency has been set to less than 24 hours.');
    }
  }

  /**
   * {@inheritdoc}.
   */
  public function getAction() {
    if ($this->score == SiteAuditCheckBase::AUDIT_CHECK_SCORE_FAIL) {
      return $this->t('Please visit /admin/config/system/cron and set the cron frequency to something other than Never but less than 24 hours.');
    }
    elseif ($this->score == SiteAuditCheckBase::AUDIT_CHECK_SCORE_WARN) {
      if ($this->registry->cron_safe_threshold > (24 * 60 * 60)) {
        return $this->t('Please visit /admin/config/system/cron and set the cron frequency to something less than 24 hours.');
      }
    }
  }

  /**
   * {@inheritdoc}.
   */
  public function calculateScore() {
    // Determine when cron last ran.
    $this->registry->cron_last = \Drupal::state()->get('system.cron_last');
    $this->registry->cron_safe_threshold = \Drupal::config('system.cron')->get('threshold.autorun');

    // Cron hasn't run in the past day.
    if ((time() - $this->registry->cron_last) > (24 * 60 * 60)) {
      if ($this->registry->cron_safe_threshold === 0) {
        return SiteAuditCheckBase::AUDIT_CHECK_SCORE_FAIL;
      }
      else {
        return SiteAuditCheckBase::AUDIT_CHECK_SCORE_WARN;
      }
    }
    elseif ($this->registry->cron_safe_threshold > (24 * 60 * 60)) {
      return SiteAuditCheckBase::AUDIT_CHECK_SCORE_WARN;
    }
    return SiteAuditCheckBase::AUDIT_CHECK_SCORE_PASS;
  }

}
