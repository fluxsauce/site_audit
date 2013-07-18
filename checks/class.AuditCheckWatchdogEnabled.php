<?php

class AuditCheckWatchdogEnabled extends AuditCheck {
  public function getLabel() {
    return dt('dblog status');
  }

  public function getResultFail() {}

  public function getResultInfo() {
    return dt('Database logging (dblog) is not enabled; if the site is having problems, consider enabling it for debugging.');
  }

  public function getResultPass() {
    return dt('Database logging (dblog) is enabled.');
  }

  public function getResultWarning() {}

  public function getAction() {}

  public function getDescription() {
    return dt('Check to see if database logging is enabled');
  }

  public function getScore() {
    if (!module_exists('dblog')) {
      $this->abort = TRUE;
      return AuditCheck::AUDIT_CHECK_SCORE_INFO;
    }
    return AuditCheck::AUDIT_CHECK_SCORE_PASS;
  }
}
