<?php

class AuditReportDatabase extends AuditReport {
  public function getLabel() {
    return dt('Database');
  }

  public function getCheckNames() {
    return array(
      'size',
      'rowcount',
      'collation',
    );
  }
}
