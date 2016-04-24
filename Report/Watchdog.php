<?php
/**
 * @file
 * Contains \SiteAudit\Report\Watchdog.
 */

/**
 * Class SiteAuditReportWatchdog.
 */
class SiteAuditReportWatchdog extends SiteAuditReportAbstract {

  /**
   * Implements \SiteAudit\Report\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Watchdog database logs');
  }

}
