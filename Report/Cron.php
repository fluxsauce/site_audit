<?php
/**
 * @file
 * Contains \SiteAudit\Report\Cron.
 */

class SiteAuditReportCron extends SiteAuditReportAbstract {
  /**
   * Implements \SiteAudit\Report\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Cron');
  }
}
