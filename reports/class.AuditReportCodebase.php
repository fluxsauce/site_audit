<?php
/**
 * @file
 * Contains \AuditReportCodebase.
 */

class AuditReportCodebase extends AuditReport {
  /**
   * Implements \AuditReport\getLabel().
   */
  public function getLabel() {
    return dt('Codebase');
  }

  /**
   * Implements \AuditReport\getCheckNames().
   */
  public function getCheckNames() {
    return array(
      'sizefiles',
      'sizeall',
    );
  }
}
