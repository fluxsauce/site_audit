<?php
/**
 * @file
 * Contains \SiteAudit\Report\StaticCodeAnalysis.
 */

/**
 * Class SiteAuditReportStaticCodeAnalysis.
 */
class SiteAuditReportStaticCodeAnalysis extends SiteAuditReportAbstract {
  /**
   * Implements \SiteAudit\Report\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Static Code Analysis');
  }

}
