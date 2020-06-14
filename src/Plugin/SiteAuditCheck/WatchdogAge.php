<?php

namespace Drupal\site_audit\Plugin\SiteAuditCheck;

use Drupal\site_audit\Plugin\SiteAuditCheckBase;

/**
 * Provides the WatchdogAge Check.
 *
 * @SiteAuditCheck(
 *  id = "watchdog_age",
 *  name = @Translation("Date range of log entries"),
 *  description = @Translation("Oldest and newest."),
 *  report = "watchdog"
 * )
 */
class WatchdogAge extends SiteAuditCheckBase {
  public $ageNewest;
  public $ageOldest;

  /**
   * {@inheritdoc}.
   */
  public function getResultFail() {}

  /**
   * {@inheritdoc}.
   */
  public function getResultInfo() {
    // If two different days...
    if (date('Y-m-d', $this->ageOldest) != date('Y-m-d', $this->ageNewest)) {
      return $this->t('From @from to @to (@days days)', [
        '@from' => date('r', $this->ageOldest),
        '@to' => date('r', $this->ageNewest),
        '@days' => round(($this->ageNewest - $this->ageOldest) / 86400, 2),
      ]);
    }
    // Same day; don't calculate number of days.
    return $this->t('From @from to @to', [
      '@from' => date('r', $this->ageOldest),
      '@to' => date('r', $this->ageNewest),
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
    // Age of oldest entry.
    $query = \Drupal::database()->select('watchdog');
    $query->addField('watchdog', 'timestamp');
    $query->orderBy('wid', 'ASC');
    $query->range(0, 1);
    $this->ageOldest = $query->execute()->fetchField();

    // Age of newest entry.
    $query = \Drupal::database()->select('watchdog');
    $query->addField('watchdog', 'timestamp');
    $query->orderBy('wid', 'DESC');
    $query->range(0, 1);
    $this->ageNewest = $query->execute()->fetchField();

    return SiteAuditCheckBase::AUDIT_CHECK_SCORE_INFO;
  }

}
