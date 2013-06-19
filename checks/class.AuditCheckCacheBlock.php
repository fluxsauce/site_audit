<?php

class AuditCheckCacheBlock extends AuditCheck {
  public function getLabel() {
    return dt('Block caching');
  }

  public function getResultFail() {
    return dt('Block caching is not enabled!');
  }

  public function getResultInfo() {}

  public function getResultPass() {
    return dt('Block caching is enabled.');
  }

  public function getResultWarning() {
    return dt('Block caching is inactive because you have enabled modules defining content access restrictions.');
  }

  public function getAction() {
    if ($this->score == AuditCheck::AUDIT_CHECK_SCORE_FAIL) {
      return dt('Go to /admin/config/development/performance and check "Cache blocks".');
    }
  }

  public function getDescription() {
    return dt('Verify Drupal\'s block caching is enabled.');
  }

  public function getScore() {
    global $conf;
    if ($conf['block_cache']) {
      return AuditCheck::AUDIT_CHECK_SCORE_PASS;
    }
    // https://api.drupal.org/api/drupal/modules!block!block.module/function/block_form_system_performance_settings_alter/7
    else if (count(module_implements('node_grants'))) {
      return AuditCheck::AUDIT_CHECK_SCORE_WARN;
    }
    return AuditCheck::AUDIT_CHECK_SCORE_FAIL;
  }
}