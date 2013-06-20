<?php

class AuditReportExtensions extends AuditReport {
  public function getLabel() {
    return dt('Extensions');
  }

  public function getCheckNames() {
    return array(
      'count',
      'dev',
      'unrecommended',
      'duplicate',
    );
  }
}
