<?php
/**
 * @file
 * Contains \SiteAudit\Report\Roles.
 */

class SiteAuditReportRoles extends SiteAuditReportAbstract {
  /**
   * Implements \SiteAudit\Report\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Roles');
  }
}
