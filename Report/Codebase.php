<?php
/**
 * @file
 * Contains \SiteAudit\Report\Codebase.
 */

/**
 * Class SiteAuditReportCodebase.
 */
class SiteAuditReportCodebase extends SiteAuditReportAbstract {

  /**
   * Implements \SiteAudit\Report\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Codebase');
  }

}
