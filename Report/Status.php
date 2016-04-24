<?php
/**
 * @file
 * Contains \SiteAudit\Report\Status.
 */

/**
 * Class SiteAuditReportStatus.
 */
class SiteAuditReportStatus extends SiteAuditReportAbstract {

  /**
   * Implements \SiteAudit\Report\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Status');
  }

}
