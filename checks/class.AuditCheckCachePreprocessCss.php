<?php

class AuditCheckCachePreprocessCss extends AuditCheck {
  public function getLabel() {
    return dt('Aggregate and compress CSS files in Drupal');
  }

  public function getResultFail() {
    return dt('CSS aggregation and compression is not enabled!');
  }

  public function getResultInfo() {}

  public function getResultPass() {
    return dt('CSS aggregation and compression is enabled.');
  }

  public function getResultWarning() {}

  public function getAction() {
    if (!in_array($this->score, array(AuditCheck::AUDIT_CHECK_SCORE_PASS))) {
      return dt('Go to /admin/config/development/performance and check "Aggregate and compress CSS files".');
    }
  }

  public function getDescription() {
    return dt('Verify that Drupal is aggregating and compressing CSS.');
  }

  public function getScore() {
    global $conf;
    if ($conf['preprocess_css']) {
      return AuditCheck::AUDIT_CHECK_SCORE_PASS;
    }
    return AuditCheck::AUDIT_CHECK_SCORE_FAIL;
  }
}
