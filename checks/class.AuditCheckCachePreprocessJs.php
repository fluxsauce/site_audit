<?php

class AuditCheckCachePreprocessJs extends AuditCheck {
  public function getLabel() {
    return dt('Aggregate JavaScript files in Drupal');
  }

  public function getResultFail() {
    return dt('JavaScript aggregation is not enabled!');
  }

  public function getResultInfo() {}

  public function getResultPass() {
    return dt('JavaScript aggregation is enabled.');
  }

  public function getResultWarning() {}

  public function getAction() {
    if (!in_array($this->score, array(AuditCheck::AUDIT_CHECK_SCORE_PASS))) {
      return dt('Go to /admin/config/development/performance and check "Aggregate JavaScript files".');
    }
  }

  public function getDescription() {
    return dt('Verify that Drupal is aggregating JavaScript.');
  }

  public function getScore() {
    global $conf;
    if ($conf['preprocess_js']) {
      return AuditCheck::AUDIT_CHECK_SCORE_PASS;
    }
    return AuditCheck::AUDIT_CHECK_SCORE_FAIL;
  }
}