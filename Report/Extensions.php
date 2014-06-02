<?php
/**
 * @file
 * Contains \SiteAudit\Report\Extensions.
 */

class SiteAuditReportExtensions extends SiteAuditReportAbstract {
  /**
   * Implements \SiteAudit\Report\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Extensions');
  }
}
