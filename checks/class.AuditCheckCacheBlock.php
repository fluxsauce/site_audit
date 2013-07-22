<?php
/**
 * @file
 * Contains \AuditCheckCacheBlock.
 */

class AuditCheckCacheBlock extends AuditCheck {
  /**
   * Implements \AuditCheck\getLabel().
   */
  public function getLabel() {
    return dt('Block caching');
  }

  /**
   * Implements \AuditCheck\getDescription().
   */
  public function getDescription() {
    return dt('Verify Drupal\'s block caching is enabled.');
  }

  /**
   * Implements \AuditCheck\getResultFail().
   */
  public function getResultFail() {
    return dt('Block caching is not enabled!');
  }

  /**
   * Implements \AuditCheck\getResultInfo().
   */
  public function getResultInfo() {}

  /**
   * Implements \AuditCheck\getResultPass().
   */
  public function getResultPass() {
    return dt('Block caching is enabled.');
  }

  /**
   * Implements \AuditCheck\getResultWarning().
   */
  public function getResultWarning() {
    return dt('Block caching is inactive because you have enabled modules defining content access restrictions.');
  }

  /**
   * Implements \AuditCheck\getAction().
   */
  public function getAction() {
    if ($this->score == AuditCheck::AUDIT_CHECK_SCORE_FAIL) {
      return dt('Go to /admin/config/development/performance and check "Cache blocks".');
    }
  }

  /**
   * Implements \AuditCheck\getScore().
   */
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
