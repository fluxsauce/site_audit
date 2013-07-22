<?php
/**
 * @file
 * Contains \AuditCheckWatchdog404.
 */

class AuditCheckWatchdog404 extends AuditCheck {
  /**
   * Implements \AuditCheck\getLabel().
   */
  public function getLabel() {
    return dt('Number of 404 entries');
  }

  /**
   * Implements \AuditCheck\getDescription().
   */
  public function getDescription() {
    return dt('Count the number of page not found entries.');
  }

  /**
   * Implements \AuditCheck\getResultFail().
   */
  public function getResultFail() {}

  /**
   * Implements \AuditCheck\getResultInfo().
   */
  public function getResultInfo() {
    return dt('@count_404 pages not found (@percent_404%).', array(
      '@count_404' => $this->registry['count_404'],
      '@percent_404' => $this->registry['percent_404'],
    ));
  }

  /**
   * Implements \AuditCheck\getResultPass().
   */
  public function getResultPass() {
    return dt('No 404 entries.');
  }

  /**
   * Implements \AuditCheck\getResultWarning().
   */
  public function getResultWarning() {
    return $this->getResultInfo();
  }

  /**
   * Implements \AuditCheck\getAction().
   */
  public function getAction() {
    if ($this->score == AuditCheck::AUDIT_CHECK_SCORE_WARN) {
      return dt('Review the full report at admin/reports/page-not-found. If self-inflicted, fix the source. If a redirect is appropriate, visit admin/config/search/path and add URL aliases.');
    }
  }

  /**
   * Implements \AuditCheck\getScore().
   */
  public function getScore() {
    $sql_query  = 'SELECT COUNT(wid) ';
    $sql_query .= 'FROM {watchdog} ';
    $sql_query .= 'WHERE type=:type';
    $this->registry['count_404'] = db_query($sql_query, array(
      ':type' => 'page not found',
    ))->fetchField();
    $this->registry['percent_404'] = 0;

    // @TODO: Aggregate 404 entries and return top 10.
    if (!$this->registry['count_404']) {
      $this->abort = TRUE;
      return AuditCheck::AUDIT_CHECK_SCORE_PASS;
    }
    $this->registry['percent_404'] = round(($this->registry['count_404'] / $this->registry['count_entries']) * 100);
    if ($this->registry['percent_404'] >= 10) {
      return AuditCheck::AUDIT_CHECK_SCORE_WARN;
    }
    return AuditCheck::AUDIT_CHECK_SCORE_INFO;
  }
}
