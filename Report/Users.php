<?php
/**
 * @file
 * Contains \SiteAudit\Report\Watchdog.
 */

class SiteAuditReportUsers extends SiteAuditReportAbstract {
  /**
   * Implements \SiteAudit\Report\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Users');
  }

  /**
   * Implements \SiteAudit\Report\Abstract\getCheckNames().
   */
  public function getCheckNames() {
    return array(
      'CountAll',
      'CountBlocked',
      'RolesList',
      'WhoIsNumberOne',
      'BlockedNumberOne'
    );
  }
}
