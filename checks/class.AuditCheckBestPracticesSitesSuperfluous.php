<?php

class AuditCheckBestPracticesSitesSuperfluous extends AuditCheck {
  public function getLabel() {
    return dt('Superfluous files in /sites');
  }

  public function getResultFail() {}

  public function getResultInfo() {}

  public function getResultPass() {
    return dt('No multisites detected.');
  }

  public function getResultWarning() {
    return dt('The following extra files were detected: @list', array(
      '@list' => implode(', ', $this->registry['superfluous']),
    ));
  }

  public function getAction() {
    if ($this->score == AuditCheck::AUDIT_CHECK_SCORE_WARN) {
      return dt('Unless you have an explicit need for it, don\'t store anything other than settings here.');
    }
  }

  public function getDescription() {
    return dt('Detect unnecessary files.');
  }

  public function getScore() {
    $drupal_root = drush_get_context('DRUSH_SELECTED_DRUPAL_ROOT');
    $handle = opendir($drupal_root . '/sites/');
    $this->registry['superfluous'] = array();
    while (FALSE !== ($entry = readdir($handle))) {
      if (!in_array($entry, array(
        '.',
        '..',
        'default',
        'all',
        'example.sites.php',
        'README.txt',
      ))) {
        if (is_file($drupal_root . '/sites/' . $entry)) {
          $this->registry['superfluous'][] = $entry;
        }
      }
    }
    closedir($handle);
    if (!empty($this->registry['superfluous'])) {
      return AuditCheck::AUDIT_CHECK_SCORE_WARN;
    }
    return AuditCheck::AUDIT_CHECK_SCORE_PASS;
  }
}
