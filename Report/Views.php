<?php
/**
 * @file
 * Contains \SiteAudit\Report\Views.
 */

class SiteAuditReportViews extends SiteAuditReportAbstract {
  /**
   * Implements \SiteAudit\Report\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Views');
  }
}
