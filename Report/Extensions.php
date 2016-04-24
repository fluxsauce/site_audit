<?php
/**
 * @file
 * Contains \SiteAudit\Report\Extensions.
 */

/**
 * Class SiteAuditReportExtensions.
 */
class SiteAuditReportExtensions extends SiteAuditReportAbstract {

  /**
   * Implements \SiteAudit\Report\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Extensions');
  }

}
