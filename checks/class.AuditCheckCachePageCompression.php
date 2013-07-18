<?php

class AuditCheckCachePageCompression extends AuditCheck {
  public function getLabel() {
    return dt('Cached page compression');
  }

  public function getResultFail() {
    if (drush_get_option('vendor') == 'pantheon') {
      return dt('Cached pages are compressed!');
    }
    else {
      return dt('Cached pages are not compressed!');
    }
  }

  public function getResultInfo() {}

  public function getResultPass() {
    if (drush_get_option('vendor') == 'pantheon') {
      return dt('Cached pages are not compressed.');
    }
    else {
      return dt('Cached pages are compressed.');
    }
  }

  public function getResultWarning() {}

  public function getAction() {
    if (!in_array($this->score, array(AuditCheck::AUDIT_CHECK_SCORE_PASS))) {
      if (drush_get_option('vendor') == 'pantheon') {
        return dt('Pantheon compresses your pages for you. Don\'t make Drupal do the work! Go to /admin/config/development/performance and uncheck "Compress cached pages".');
      }
      else {
        return dt('Go to /admin/config/development/performance and check "Compress cached pages".');
      }
    }
  }

  public function getDescription() {
    if (drush_get_option('vendor') == 'pantheon') {
      return dt('Verify that Drupal is not set to compress cached pages.');
    }
    else {
      return dt('Verify that Drupal is set to compress cached pages.');
    }
  }

  public function getScore() {
    global $conf;
    if (drush_get_option('vendor') == 'pantheon') {
      if (!$conf['page_compression']) {
        return AuditCheck::AUDIT_CHECK_SCORE_PASS;
      }
      return AuditCheck::AUDIT_CHECK_SCORE_FAIL;
    }
    if (!$conf['page_compression']) {
      return AuditCheck::AUDIT_CHECK_SCORE_FAIL;
    }
    return AuditCheck::AUDIT_CHECK_SCORE_PASS;
  }
}
