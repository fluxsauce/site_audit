<?php
/**
 * @file
 * Contains \SiteAudit\Report\Cache.
 */

class SiteAuditReportCache extends SiteAuditReportAbstract {
  /**
   * Implements \SiteAudit\Report\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Drupal\'s caching settings');
  }

  /**
   * Implements \SiteAudit\Report\Abstract\getCheckNames().
   */
  public function getCheckNames() {
    return array(
      'Anon',
      'Block',
      'Lifetime',
      'PageExpire',
      'PageCompression',
      'PreprocessCss',
      'PreprocessJs',
    );
  }
}
