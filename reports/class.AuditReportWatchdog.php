<?php
/**
 * @file
 * Contains \AuditReportWatchdog.
 */

class AuditReportWatchdog extends AuditReport {
  /**
   * Implements \AuditReport\getLabel().
   */
  public function getLabel() {
    return dt('Watchdog database logs');
  }

  /**
   * Implements \AuditReport\getCheckNames().
   */
  public function getCheckNames() {
    return array(
      'enabled',
      'count',
      'age',
      '404',
      'php',
    );
  }
}
