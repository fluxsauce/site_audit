<?php

class AuditCheckCacheLifetime extends AuditCheck {
  public function getLabel() {
    return dt('Minimum cache lifetime');
  }

  public function getResultFail() {}

  public function getResultInfo() {}

  public function getResultPass() {
    return dt('Minimum cache lifetime is set to <none>.');
  }

  public function getResultWarning() {
    global $conf;
    return dt('Minimum cache lifetime is set to @minutes min.', array(
      '@minutes' => round($conf['cache_lifetime'] / 60),
    ));
  }

  public function getAction() {
    if (!in_array($this->score, array(AuditCheck::AUDIT_CHECK_SCORE_PASS))) {
      return dt('Go to /admin/config/development/performance and set "Minimum cache lifetime" to <none>.');
    }
  }

  public function getDescription() {
    return dt('Verify that Drupal\'s minimum cache lifetime is set to never expire.');
  }

  public function getScore() {
    global $conf;
    if (!$conf['cache_lifetime']) {
      return AuditCheck::AUDIT_CHECK_SCORE_PASS;
    }
    return AuditCheck::AUDIT_CHECK_SCORE_WARN;
  }
}