<?php
/**
 * @file
 * Contains \AuditCheckBestPracticesSettings.
 */

class AuditCheckBestPracticesSettings extends AuditCheck {
  /**
   * Implements \AuditCheck\getLabel().
   */
  public function getLabel() {
    return dt('sites/default/settings.php');
  }

  /**
   * Implements \AuditCheck\getDescription().
   */
  public function getDescription() {
    return dt('Check if the configuration file exists.');
  }

  /**
   * Implements \AuditCheck\getResultFail().
   */
  public function getResultFail() {}

  /**
   * Implements \AuditCheck\getResultInfo().
   */
  public function getResultInfo() {}

  /**
   * Implements \AuditCheck\getResultPass().
   */
  public function getResultPass() {
    return dt('settings.php exists and is not a symbolic link.');
  }

  /**
   * Implements \AuditCheck\getResultWarning().
   */
  public function getResultWarning() {
    return dt('sites/default/settings.php is a symbolic link.');
  }

  /**
   * Implements \AuditCheck\getAction().
   */
  public function getAction() {
    if ($this->score == AuditCheck::AUDIT_CHECK_SCORE_WARN) {
      return dt('Don\'t rely on symbolic links for core configuration files; copy settings.php where it should be and remove the symbolic link.');
    }
    if ($this->score == AuditCheck::AUDIT_CHECK_SCORE_FAIL) {
      return dt('Even if environment settings are injected, create a stub settings.php file for compatibility.');
    }
  }

  /**
   * Implements \AuditCheck\calculateScore().
   */
  public function calculateScore() {
    $drupal_root = drush_get_context('DRUSH_SELECTED_DRUPAL_ROOT');
    if (file_exists($drupal_root . '/sites/default/settings.php')) {
      if (is_link($drupal_root . '/sites/default/settings.php')) {
        return AuditCheck::AUDIT_CHECK_SCORE_WARN;
      }
      return AuditCheck::AUDIT_CHECK_SCORE_PASS;
    }
    return AuditCheck::AUDIT_CHECK_SCORE_FAIL;
  }
}
