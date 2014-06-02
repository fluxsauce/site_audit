<?php
/**
 * @file
 * Contains \SiteAudit\Report\Content.
 */

class SiteAuditReportContent extends SiteAuditReportAbstract {
  /**
   * Implements \SiteAudit\Report\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Content');
  }
}
