<?php

class AuditCheckCachePageExpire extends AuditCheck {
  public function getLabel() {
    return dt('Expiration of cached pages');
  }

  public function getResultFail() {
    return dt('Expiration of cached pages not set!');
  }

  public function getResultInfo() {}

  public function getResultPass() {
    global $conf;
    return dt('Expiration of cached pages is set to @minutes min.', array(
      '@minutes' => round($conf['page_cache_maximum_age'] / 60),
    ));
  }

  public function getResultWarning() {
    global $conf;
    return dt('Expiration of cached pages only set to @minutes min.', array(
      '@minutes' => round($conf['page_cache_maximum_age'] / 60),
    ));
  }

  public function getAction() {
    if (!in_array($this->score, array(AuditCheck::AUDIT_CHECK_SCORE_PASS))) {
      return dt('Go to /admin/config/development/performance and set "Expiration of cached pages" to 15 min or above.');
    }
  }

  public function getDescription() {
    return dt('Verify that Drupal\'s cached pages last for at least 15 minutes.');
  }

  public function getScore() {
    global $conf;
    if ($conf['page_cache_maximum_age'] >= 900) {
      return AuditCheck::AUDIT_CHECK_SCORE_PASS;
    }
    elseif (!$conf['page_cache_maximum_age']) {
      return AuditCheck::AUDIT_CHECK_SCORE_FAIL;
    }
    return AuditCheck::AUDIT_CHECK_SCORE_WARN;
  }
}