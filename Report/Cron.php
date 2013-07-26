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

  /**
   * Implements \SiteAudit\Report\Abstract\getCheckNames().
   */
  public function getCheckNames() {
    return array(
      'Enabled',
      'Running',
      'Last',
    );
  }
}
