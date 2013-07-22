<?php
/**
 * @file
 * Contains \AuditReportCache.
 */

class AuditReportCache extends AuditReport {
  /**
   * Implements \AuditReport\getLabel().
   */
  public function getLabel() {
    return dt('Drupal\'s caching settings');
  }

  /**
   * Implements \AuditReport\getCheckNames().
   */
  public function getCheckNames() {
    return array(
      'anon',
      'block',
      'lifetime',
      'pageExpire',
      'pageCompression',
      'preprocessCss',
      'preprocessJs',
    );
  }
}
