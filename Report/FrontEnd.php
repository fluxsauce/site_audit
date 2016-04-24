<?php
/**
 * @file
 * Contains \SiteAudit\Report\FrontEnd.
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
