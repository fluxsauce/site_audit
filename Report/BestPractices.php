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
}
