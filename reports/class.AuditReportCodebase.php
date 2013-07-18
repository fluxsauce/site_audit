<?php

class AuditReportCodebase extends AuditReport {
  public function getLabel() {
    return dt('Codebase');
  }

  public function getCheckNames() {
    return array(
      'sizefiles',
      'sizeall',
    );
  }
}
