<?php
/**
 * @file
 * Contains \SiteAudit\Report\Insights.
 */

/**
 * Class SiteAuditReportFrontEnd.
 */
class SiteAuditReportFrontEnd extends SiteAuditReportAbstract {

  /**
   * Implements \SiteAudit\Report\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Front End');
  }

}
