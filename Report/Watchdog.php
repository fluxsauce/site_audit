<?php
/**
 * @file
 * Contains \SiteAudit\Report\Watchdog.
 */

class SiteAuditReportWatchdog extends SiteAuditReportAbstract {
  /**
   * Implements \SiteAudit\Report\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Watchdog database logs');
  }

  /**
   * Implements \SiteAudit\Report\Abstract\getCheckNames().
   */
  public function getCheckNames() {
    return array(
      'Syslog',
      'Enabled',
      'Count',
      'Age',
      '404',
      'Php',
    );
  }
}
