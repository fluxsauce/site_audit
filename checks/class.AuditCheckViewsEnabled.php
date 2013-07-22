<?php
/**
 * @file
 * Contains \AuditCheckViewsEnabled.
 */

class AuditCheckViewsEnabled extends AuditCheck {
  /**
   * Implements \AuditCheck\getLabel().
   */
  public function getLabel() {
    return dt('Views status');
  }

  /**
   * Implements \AuditCheck\getDescription().
   */
  public function getDescription() {
    return dt('Check to see if enabled');
  }

  /**
   * Implements \AuditCheck\getResultFail().
   */
  public function getResultFail() {}

  /**
   * Implements \AuditCheck\getResultInfo().
   */
  public function getResultInfo() {
    return dt('Views is not enabled.');
  }

  /**
   * Implements \AuditCheck\getResultPass().
   */
  public function getResultPass() {
    return dt('Views is enabled.');
  }

  /**
   * Implements \AuditCheck\getResultWarning().
   */
  public function getResultWarning() {
    return dt('Only Views 7.x-3.x is supported by this tool.');
  }

  /**
   * Implements \AuditCheck\getAction().
   */
  public function getAction() {}

  /**
   * Implements \AuditCheck\calculateScore().
   */
  public function calculateScore() {
    if (!module_exists('views')) {
      $this->abort = TRUE;
      return AuditCheck::AUDIT_CHECK_SCORE_INFO;
    }
    $info = drupal_parse_info_file(drupal_get_path('module', 'views') . '/views.info');
    if (version_compare($info['version'], '7.x-3.0') >= 0) {
      return AuditCheck::AUDIT_CHECK_SCORE_PASS;
    }
    return AuditCheck::AUDIT_CHECK_SCORE_WARN;
  }
}
