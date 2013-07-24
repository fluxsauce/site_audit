<?php
/**
 * @file
 * Contains \SiteAudit\Check\Watchdog\Age.
 */

class SiteAuditCheckWatchdogAge extends SiteAuditCheckAbstract {
  public $ageNewest;
  public $ageOldest;

  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Date range of log entries');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Oldest and newest.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getResultInfo().
   */
  public function getResultInfo() {
    // If two different days...
    if (date('Y-m-d', $this->ageOldest) != date('Y-m-d', $this->ageNewest)) {
      return dt('From @from to @to (@days days)', array(
        '@from' => date('r', $this->ageOldest),
        '@to' => date('r', $this->ageNewest),
        '@days' => round(($this->ageNewest - $this->ageOldest) / 86400, 2),
      ));
    }
    // Same day; don't calculate number of days.
    return dt('From @from to @to', array(
      '@from' => date('r', $this->ageOldest),
      '@to' => date('r', $this->ageNewest),
    ));
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultPass().
   */
  public function getResultPass() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getResultWarn().
   */
  public function getResultWarn() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getAction().
   */
  public function getAction() {}

  /**
   * Implements \SiteAudit\Check\Abstract\calculateScore().
   */
  public function calculateScore() {
    // Age of oldest entry.
    $sql_query  = 'SELECT timestamp ';
    $sql_query .= 'FROM {watchdog} ';
    $sql_query .= 'ORDER BY wid ASC ';
    $sql_query .= 'LIMIT 1 ';
    $this->ageOldest = db_query($sql_query)->fetchField();

    // Age of newest entry.
    $sql_query  = 'SELECT timestamp ';
    $sql_query .= 'FROM {watchdog} ';
    $sql_query .= 'ORDER BY wid DESC ';
    $sql_query .= 'LIMIT 1 ';
    $this->ageNewest = db_query($sql_query)->fetchField();

    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO;
  }
}
