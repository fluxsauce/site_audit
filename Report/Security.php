<?php
/**
 * @file
 * Contains \SiteAudit\Report\Security.
 */

class SiteAuditReportSecurity extends SiteAuditReportAbstract {
  /**
   * Implements \SiteAudit\Report\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Security');
  }
}
