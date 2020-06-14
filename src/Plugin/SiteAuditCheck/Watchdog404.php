<?php

namespace Drupal\site_audit\Plugin\SiteAuditCheck;

use Drupal\site_audit\Plugin\SiteAuditCheckBase;

/**
 * Provides the Watchdog404 Check.
 *
 * @SiteAuditCheck(
 *  id = "watchdog_404",
 *  name = @Translation("Number of 404 entries"),
 *  description = @Translation("Count the number of page not found entries."),
 *  report = "watchdog"
 * )
 */
class Watchdog404 extends SiteAuditCheckBase {

  /**
   * {@inheritdoc}.
   */
  public function getResultFail() {}

  /**
   * {@inheritdoc}.
   */
  public function getResultInfo() {
    return $this->t('@count_404 pages not found (@percent_404%).', [
      '@count_404' => $this->registry->count_404,
      '@percent_404' => $this->registry->percent_404,
    ]);
  }

  /**
   * {@inheritdoc}.
   */
  public function getResultPass() {
    return $this->t('No 404 entries.');
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
    if ($this->score == SiteAuditCheckBase::AUDIT_CHECK_SCORE_WARN) {
      return $this->t('Review the full report at admin/reports/page-not-found. If self-inflicted, fix the source. If a redirect is appropriate, visit admin/config/search/path and add URL aliases.');
    }
  }

  /**
   * {@inheritdoc}.
   */
  public function calculateScore() {
    if (empty($this->registry->count_entries)) {
      $this->checkInvokeCalculateScore('watchdog_count');
    }
    if (!$this->registry->watchdog_enabled) {
      return;
    }
    $query = \Drupal::database()->select('watchdog');
    $query->addExpression('COUNT(wid)', 'count');
    $query->condition('type', 'page not found');
    $this->registry->count_404 = $query->execute()->fetchField();

    $this->registry->percent_404 = 0;

    // @TODO: Aggregate 404 entries and return top 10.
    if (!$this->registry->count_404) {
      return SiteAuditCheckBase::AUDIT_CHECK_SCORE_PASS;
    }
    $this->registry->percent_404 = round(($this->registry->count_404 / $this->registry->count_entries) * 100);
    if ($this->registry->percent_404 >= 10) {
      return SiteAuditCheckBase::AUDIT_CHECK_SCORE_WARN;
    }
    return SiteAuditCheckBase::AUDIT_CHECK_SCORE_INFO;
  }

}
