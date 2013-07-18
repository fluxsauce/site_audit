<?php

class AuditCheckBestPracticesSitesAll extends AuditCheck {
  public function getLabel() {
    return dt('sites/all');
  }

  public function getResultFail() {
    return dt('sites/all does not exist!');
  }

  public function getResultInfo() {}

  public function getResultPass() {
    return dt('sites/all is a directory and not a symbolic link.');
  }

  public function getResultWarning() {
    return dt('sites/all exists as a symbolic link.');
  }

  public function getAction() {
    if ($this->score == AuditCheck::AUDIT_CHECK_SCORE_FAIL) {
      return dt('sites/all is necessary; recreate the directory immediately.');
    }
    if ($this->score == AuditCheck::AUDIT_CHECK_SCORE_WARN) {
      return dt('Avoid changing Drupal\'s site structure; remove the symbolic link and recreate sites/all.');
    }
  }

  public function getDescription() {
    return dt('Check if it exists and isn\'t symbolic');
  }

  public function getScore() {
    $drupal_root = drush_get_context('DRUSH_SELECTED_DRUPAL_ROOT');
    if (is_dir($drupal_root . '/sites/all')) {
      if (is_link($drupal_root . '/sites/all')) {
        return AuditCheck::AUDIT_CHECK_SCORE_WARN;
      }
      return AuditCheck::AUDIT_CHECK_SCORE_PASS;
    }
    return AuditCheck::AUDIT_CHECK_SCORE_FAIL;
  }
}
