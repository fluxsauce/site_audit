<?php
/**
 * @file
 * Contains \SiteAudit\Report\BestPractices.
 */

/**
 * Class SiteAuditReportBestPractices.
 */
class SiteAuditReportBestPractices extends SiteAuditReportAbstract {

  /**
   * Implements \SiteAudit\Report\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Best practices');
  }

}
