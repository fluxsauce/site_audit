<?php
/**
 * @file
 * Contains \AuditReportInsights.
 */

class AuditReportInsights extends AuditReport {
  /**
   * Override parent constructor to provide argument support.
   *
   * @param string $key
   *   Google API key.
   * @param string $url
   *   URL of site to test.
   */
  public function __construct($key, $url) {
    $this->registry['key'] = $key;
    $this->registry['url'] = $url;
    parent::__construct();
  }

  /**
   * Implements \AuditReport\getLabel().
   */
  public function getLabel() {
    return dt('Google PageSpeed Insights');
  }

  /**
   * Implements \AuditReport\getCheckNames().
   */
  public function getCheckNames() {
    return array(
      'analyze',
    );
  }
}
