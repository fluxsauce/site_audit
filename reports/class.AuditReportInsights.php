<?php

class AuditReportInsights extends AuditReport {
  public function __construct($key, $url) {
    $this->registry['key'] = $key;
    $this->registry['url'] = $url;
    parent::__construct();
  }

  public function getLabel() {
    return dt('Google PageSpeed Insights');
  }

  public function getCheckNames() {
    return array(
      'analyze',
    );
  }
}
