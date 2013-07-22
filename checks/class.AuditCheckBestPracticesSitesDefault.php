<?php
/**
 * @file
 * Contains \AuditCheckBestPracticesSitesDefault.
 */

class AuditCheckBestPracticesSitesDefault extends AuditCheck {
  /**
   * Implements \AuditCheck\getLabel().
   */
  public function getLabel() {
    return dt('sites/default');
  }

  /**
   * Implements \AuditCheck\getDescription().
   */
  public function getDescription() {
    return dt('Check if it exists and isn\'t symbolic');
  }

  /**
   * Implements \AuditCheck\getResultFail().
   */
  public function getResultFail() {
    return dt('sites/default does not exist!');
  }

  /**
   * Implements \AuditCheck\getResultInfo().
   */
  public function getResultInfo() {}

  /**
   * Implements \AuditCheck\getResultPass().
   */
  public function getResultPass() {
    return dt('sites/default is a directory and not a symbolic link.');
  }

  /**
   * Implements \AuditCheck\getResultWarning().
   */
  public function getResultWarning() {
    return dt('sites/default exists as a symbolic link.');
  }

  /**
   * Implements \AuditCheck\getAction().
   */
  public function getAction() {
    if ($this->score == AuditCheck::AUDIT_CHECK_SCORE_FAIL) {
      return dt('sites/default is necessary; recreate the directory immediately.');
    }
    if ($this->score == AuditCheck::AUDIT_CHECK_SCORE_WARN) {
      return dt('Avoid changing Drupal\'s site structure; remove the symbolic link and recreate sites/default.');
    }
  }

  /**
   * Implements \AuditCheck\calculateScore().
   */
  public function calculateScore() {
    $drupal_root = drush_get_context('DRUSH_SELECTED_DRUPAL_ROOT');
    if (is_dir($drupal_root . '/sites/default')) {
      if (is_link($drupal_root . '/sites/default')) {
        return AuditCheck::AUDIT_CHECK_SCORE_WARN;
      }
      return AuditCheck::AUDIT_CHECK_SCORE_PASS;
    }
    return AuditCheck::AUDIT_CHECK_SCORE_FAIL;
  }
}
