<?php

class AuditReportCache extends AuditReport {
  public function getLabel() {
    return dt('Drupal\'s caching settings');
  }

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
