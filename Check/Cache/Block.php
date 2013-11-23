<?php
/**
 * @file
 * Contains \SiteAudit\Check\Cache\Block.
 */

class SiteAuditCheckCacheBlock extends SiteAuditCheckAbstract {
  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Block caching');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Verify Drupal\'s block caching is enabled.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {
    return dt('Block caching is not enabled!');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultInfo().
   */
  public function getResultInfo() {
    if ($this->registry['block_disabled']) {
      return dt('Block module is not enabled.');
    }
    elseif ($this->registry['no_theme_default']) {
      return dt("No default theme, so assuming blocks aren't used.");
    }
    return dt('Not checking block cache.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultPass().
   */
  public function getResultPass() {
    return dt('Block caching is enabled.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultWarn().
   */
  public function getResultWarn() {
    return dt('Block caching is inactive because you have enabled modules defining content access restrictions.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getAction().
   */
  public function getAction() {
    if ($this->score == SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL) {
      return dt('Go to /admin/config/development/performance and check "Cache blocks".');
    }
  }

  /**
   * Implements \SiteAudit\Check\Abstract\calculateScore().
   */
  public function calculateScore() {
    // See if block module is enabled.
    if (!module_exists('block')) {
      $this->registry['block_disabled'] = 'module';
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO;
    }
    // Block module is enabled, but is there a default theme?
    if (!variable_get('theme_default')) {
      $this->registry['block_disabled'] = 'no_theme_default';
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO;
    }

    global $conf;
    if ($conf['block_cache']) {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS;
    }
    // Same as block_form_system_performance_settings_alter().
    elseif (count(module_implements('node_grants'))) {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN;
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL;
  }
}
