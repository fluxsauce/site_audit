<?php
/**
 * @file
 * Contains \SiteAudit\Report\Block.
 */

class SiteAuditReportBlock extends SiteAuditReportAbstract {
  /**
   * Implements \SiteAudit\Report\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Block');
  }

  /**
   * Implements \SiteAudit\Report\Abstract\getCheckNames().
   */
  public function getCheckNames() {
    return array(
      'Enabled',
      'Cache',
      'CacheReport',
    );
  }
}
