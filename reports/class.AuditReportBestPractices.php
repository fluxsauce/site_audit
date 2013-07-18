<?php

class AuditReportBestPractices extends AuditReport {
  public function getLabel() {
    return dt('Best practices');
  }

  public function getCheckNames() {
    return array(
      'settings',
      'sitesdefault',
      'sitesall',
      'multisite',
      'sitessuperfluous',
      'fast404',
    );
  }
}
