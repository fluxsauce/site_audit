<?php
/**
 * @file
 * Contains \AuditReportExtensions.
 */

class AuditReportExtensions extends AuditReport {
  /**
   * Implements \AuditReport\getLabel().
   */
  public function getLabel() {
    return dt('Extensions');
  }

  /**
   * Implements \AuditReport\getCheckNames().
   */
  public function getCheckNames() {
    return array(
      'count',
      'dev',
      'unrecommended',
      'duplicate',
      'missing',
    );
  }
}
