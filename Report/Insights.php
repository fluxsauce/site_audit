<?php
/**
 * @file
 * Contains \SiteAudit\Report\Insights.
 */

class SiteAuditReportInsights extends SiteAuditReportAbstract {
  /**
   * Override parent constructor to provide argument support.
   *
   * @param string $url
   *   URL of site to test.
   * @param string $key
   *   Google API key.
   */
  public function __construct($url, $key) {
    $this->registry['url'] = $url;
    $this->registry['key'] = $key;
    parent::__construct();
  }

  /**
   * Implements \SiteAudit\Report\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Google PageSpeed Insights');
  }
}
