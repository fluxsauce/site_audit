<?php
/**
 * @file
 * Contains \AuditReportDatabase.
 */

class AuditReportDatabase extends AuditReport {
  /**
   * Implements \AuditReport\getLabel().
   */
  public function getLabel() {
    return dt('Database');
  }

  /**
   * Implements \AuditReport\getCheckNames().
   */
  public function getCheckNames() {
    return array(
      'size',
      'rowcount',
      'collation',
    );
  }
}
