<?php
/**
 * @file
 * Contains \SiteAudit\Report\Users.
 */

class SiteAuditReportUsers extends SiteAuditReportAbstract {
  /**
   * Implements \SiteAudit\Report\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Users');
  }
}
