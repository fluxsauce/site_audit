<?php

class AuditReportViews extends AuditReport {
  public function getLabel() {
    return dt('Views');
  }

  public function getCheckNames() {
    return array(
      'enabled',
      'count',
      'cacheresults',
      'cacheoutput',
    );
  }
}
