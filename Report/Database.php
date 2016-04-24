<?php
/**
 * @file
 * Contains \SiteAudit\Report\Database.
 */

/**
 * Class SiteAuditReportDatabase.
 */
class SiteAuditReportDatabase extends SiteAuditReportAbstract {

  /**
   * Implements \SiteAudit\Report\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Database');
  }

}
