<?php
/**
 * @file
 * Contains \AuditCheckWatchdogAge.
 */

class AuditCheckWatchdogAge extends AuditCheck {
  public $ageNewest;
  public $ageOldest;

  /**
   * Implements \AuditCheck\getLabel().
   */
  public function getLabel() {
    return dt('Date range of log entries');
  }

  /**
   * Implements \AuditCheck\getDescription().
   */
  public function getDescription() {
    return dt('Oldest and newest.');
  }

  /**
   * Implements \AuditCheck\getResultFail().
   */
  public function getResultFail() {}

  /**
   * Implements \AuditCheck\getResultInfo().
   */
  public function getResultInfo() {
    // If two different days...
    if (date('Y-m-d', $this->ageOldest) != date('Y-m-d', $this->ageNewest)) {
      return dt('From @from to @to (@days days)', array(
        '@from' => date('r', $this->ageOldest),
        '@to' => date('r', $this->ageNewest),
        '@days' => round(($this->ageNewest - $this->ageOldest) / 86400, 2)
      ));
    }
    // Same day; don't calculate number of days.
    return dt('From @from to @to', array(
      '@from' => date('r', $this->ageOldest),
      '@to' => date('r', $this->ageNewest),
    ));
  }

  /**
   * Implements \AuditCheck\getResultPass().
   */
  public function getResultPass() {}

  /**
   * Implements \AuditCheck\getResultWarning().
   */
  public function getResultWarning() {}

  /**
   * Implements \AuditCheck\getAction().
   */
  public function getAction() {}

  /**
   * Implements \AuditCheck\getScore().
   */
  public function getScore() {
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

    return AuditCheck::AUDIT_CHECK_SCORE_INFO;
  }
}
