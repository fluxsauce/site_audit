<?php
/**
 * @file
 * Contains \SiteAudit\Report\Roles.
 */

/**
 * Class SiteAuditReportRoles.
 */
class SiteAuditReportRoles extends SiteAuditReportAbstract {

  /**
   * Implements \SiteAudit\Report\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Roles');
  }

}
