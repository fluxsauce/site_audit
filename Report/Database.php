<?php
/**
 * @file
 * Contains \SiteAudit\Report\Database.
 */

class SiteAuditReportDatabase extends SiteAuditReportAbstract {
  /**
   * Implements \SiteAudit\Report\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Database');
  }

  /**
   * Implements \SiteAudit\Report\Abstract\getCheckNames().
   */
  public function getCheckNames() {
    return array(
      'Size',
      'RowCount',
      'Collation',
      'Engine',
    );
  }
}
