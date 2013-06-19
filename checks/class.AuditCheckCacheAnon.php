<?php

class AuditCheckCacheAnon extends AuditCheck {
  public function getLabel() {
    return dt('Anonymous caching');
  }

  public function getResultFail() {
    return dt('Anonymous page caching is not enabled!');
  }

  public function getResultInfo() {}

  public function getResultPass() {
    return dt('Anonymous caching is enabled.');
  }

  public function getResultWarning() {}

  public function getAction() {
    if ($this->score == AuditCheck::AUDIT_CHECK_SCORE_FAIL) {
      return dt('Go to /admin/config/development/performance and check "Cache pages for anonymous users".');
    }
  }

  public function getDescription() {
    return dt('Verify Drupal\'s anonymous page caching is enabled.');
  }

  public function getScore() {
    global $conf;
    if ($conf['cache']) {
      return AuditCheck::AUDIT_CHECK_SCORE_PASS;
    }
    return AuditCheck::AUDIT_CHECK_SCORE_FAIL;
  }
}