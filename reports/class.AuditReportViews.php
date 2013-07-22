<?php
/**
 * @file
 * Contains \AuditReportViews.
 */

class AuditReportViews extends AuditReport {
  /**
   * Implements \AuditReport\getLabel().
   */
  public function getLabel() {
    return dt('Views');
  }

  /**
   * Implements \AuditReport\getCheckNames().
   */
  public function getCheckNames() {
    return array(
      'enabled',
      'count',
      'cacheresults',
      'cacheoutput',
    );
  }
}
