<?php

class AuditReportWatchdog extends AuditReport {
  public function getLabel() {
    return dt('Watchdog database logs');
  }

  public function getCheckNames() {
    return array(
      'enabled',
      'count',
      'age',
      '404',
      'php',
    );
  }
}
