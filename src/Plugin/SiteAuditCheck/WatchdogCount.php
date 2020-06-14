<?php

namespace Drupal\site_audit\Plugin\SiteAuditCheck;

use Drupal\site_audit\Plugin\SiteAuditCheckBase;

/**
 * Provides the WatchdogCount Check.
 *
 * @SiteAuditCheck(
 *  id = "watchdog_count",
 *  name = @Translation("Count"),
 *  description = @Translation("Number of dblog entries."),
 *  report = "watchdog",
 *  weight = -3,
 * )
 */
class WatchdogCount extends SiteAuditCheckBase {

  /**
   * {@inheritdoc}.
   */
  public function getResultFail() {}

  /**
   * {@inheritdoc}.
   */
  public function getResultInfo() {
    if (!$this->registry->count_entries) {
      return $this->t('There are no dblog entries.');
    }
    return $this->t('There are @count_entries log entries.', [
      '@count_entries' => number_format($this->registry->count_entries),
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
    if (!isset($this->registry->watchdog_enabled)) {
      $this->checkInvokeCalculateScore('watchdog_enabled');
    }
    if (!$this->registry->watchdog_enabled) {
      return;
    }
    $query = \Drupal::database()->select('watchdog');
    $query->addExpression('COUNT(wid)', 'count');

    $this->registry->count_entries = $query->execute()->fetchField();
    if (!$this->registry->count_entries) {
      $this->abort = TRUE;
    }
    return SiteAuditCheckBase::AUDIT_CHECK_SCORE_INFO;
  }

}
