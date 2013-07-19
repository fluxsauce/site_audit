<?php

class AuditCheckViewsEnabled extends AuditCheck {
  public function getLabel() {
    return dt('Views status');
  }

  public function getResultFail() {}

  public function getResultInfo() {
    return dt('Views is not enabled.');
  }

  public function getResultPass() {
    return dt('Views is enabled.');
  }

  public function getResultWarning() {
    return dt('Only Views 7.x-3.x is supported by this tool.');
  }

  public function getAction() {}

  public function getDescription() {
    return dt('Check to see if enabled');
  }

  public function getScore() {
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
