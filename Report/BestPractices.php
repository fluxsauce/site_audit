<?php
/**
 * @file
 * Contains \SiteAudit\Report\BestPractices.
 */

class SiteAuditReportBestPractices extends SiteAuditReportAbstract {
  /**
   * Implements \SiteAudit\Report\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Best practices');
  }

  /**
   * Implements \SiteAudit\Report\Abstract\getCheckNames().
   */
  public function getCheckNames() {
    return array(
      'Settings',
      'SitesDefault',
      'SitesAll',
      'Multisite',
      'SitesSuperfluous',
      'Fast404',
      'PhpFilter',
    );
  }
}
