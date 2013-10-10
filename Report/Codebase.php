<?php
/**
 * @file
 * Contains \SiteAudit\Report\Codebase.
 */

class SiteAuditReportCodebase extends SiteAuditReportAbstract {
  /**
   * Implements \SiteAudit\Report\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Codebase');
  }

  /**
   * Implements \SiteAudit\Report\Abstract\getCheckNames().
   */
  public function getCheckNames() {
    return array(
      'SizeFiles',
      'SizeAll',
      'ManagedFileCount',
      'ManagedFileSize',
    );
  }
}
