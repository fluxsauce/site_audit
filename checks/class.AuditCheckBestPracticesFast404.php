<?php

class AuditCheckBestPracticesFast404 extends AuditCheck {
  public function getLabel() {
    return dt('Fast 404 pages');
  }

  public function getResultFail() {}

  public function getResultInfo() {}

  public function getResultPass() {
    return dt('Fast 404 pages are enabled.');
  }

  public function getResultWarning() {
    return dt('Fast 404 pages are not enabled.');
  }

  public function getAction() {
    if ($this->score == AuditCheck::AUDIT_CHECK_SCORE_WARN) {
      return dt('See https://drupal.org/node/1313592 and default.settings.php for details on how to implement.');
    }
  }

  public function getDescription() {
    return dt('Check if enabled.');
  }

  public function getScore() {
    global $conf;
    if ($conf['404_fast_html']) {
      return AuditCheck::AUDIT_CHECK_SCORE_PASS;
      drush_log(dt('Fast 404 pages enabled.'), 'ok');
    }
    return AuditCheck::AUDIT_CHECK_SCORE_WARN;
  }
}
