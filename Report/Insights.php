<?php
/**
 * @file
 * Contains \SiteAudit\Report\Insights.
 */

class SiteAuditReportInsights extends SiteAuditReportAbstract {
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
   * Implements \SiteAudit\Report\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Google PageSpeed Insights');
  }

  /**
   * Implements \SiteAudit\Report\Abstract\getCheckNames().
   */
  public function getCheckNames() {
    return array(
      'Analyze',
    );
  }
}
