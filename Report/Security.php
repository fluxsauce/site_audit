<?php
/**
 * @file
 * Contains \SiteAudit\Report\Security.
 */

/**
 * Class SiteAuditReportSecurity.
 */
class SiteAuditReportSecurity extends SiteAuditReportAbstract {

  /**
   * Implements \SiteAudit\Report\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Security');
  }

}
