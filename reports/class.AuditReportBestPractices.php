<?php
/**
 * @file
 * Contains \AuditReportBestPractices.
 */

class AuditReportBestPractices extends AuditReport {
  /**
   * Implements \AuditReport\getLabel().
   */
  public function getLabel() {
    return dt('Best practices');
  }

  /**
   * Implements \AuditReport\getCheckNames().
   */
  public function getCheckNames() {
    return array(
      'settings',
      'sitesdefault',
      'sitesall',
      'multisite',
      'sitessuperfluous',
      'fast404',
    );
  }
}
