<?php
/**
 * @file
 * Contains \AuditCheckBestPracticesSitesSuperfluous.
 */

class AuditCheckBestPracticesSitesSuperfluous extends AuditCheck {
  /**
   * Implements \AuditCheck\getLabel().
   */
  public function getLabel() {
    return dt('Superfluous files in /sites');
  }

  /**
   * Implements \AuditCheck\getDescription().
   */
  public function getDescription() {
    return dt('Detect unnecessary files.');
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
    return dt('No multisites detected.');
  }

  /**
   * Implements \AuditCheck\getResultWarning().
   */
  public function getResultWarning() {
    return dt('The following extra files were detected: @list', array(
      '@list' => implode(', ', $this->registry['superfluous']),
    ));
  }

  /**
   * Implements \AuditCheck\getAction().
   */
  public function getAction() {
    if ($this->score == AuditCheck::AUDIT_CHECK_SCORE_WARN) {
      return dt('Unless you have an explicit need for it, don\'t store anything other than settings here.');
    }
  }

  /**
   * Implements \AuditCheck\getScore().
   */
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
