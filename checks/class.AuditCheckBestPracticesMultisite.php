<?php
/**
 * @file
 * Contains \AuditCheckBestPracticesMultisite.
 */

class AuditCheckBestPracticesMultisite extends AuditCheck {
  /**
   * Implements \AuditCheck\getLabel().
   */
  public function getLabel() {
    return dt('Multisite');
  }

  /**
   * Implements \AuditCheck\getDescription().
   */
  public function getDescription() {
    return dt('Detect multisite configurations.');
  }

  /**
   * Implements \AuditCheck\getResultFail().
   */
  public function getResultFail() {
    return dt('The following multisite configuration(s) were detected: @list', array(
      '@list' => implode(', ', $this->registry['multisites']),
    ));
  }

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
  public function getResultWarning() {}

  /**
   * Implements \AuditCheck\getAction().
   */
  public function getAction() {
    if ($this->score == AuditCheck::AUDIT_CHECK_SCORE_FAIL) {
      return dt('See https://www.getpantheon.com/blog/much-ado-about-drupal-multisite for details.');
    }
  }

  /**
   * Implements \AuditCheck\getScore().
   */
  public function getScore() {
    $drupal_root = drush_get_context('DRUSH_SELECTED_DRUPAL_ROOT');
    $handle = opendir($drupal_root . '/sites/');
    $this->registry['multisites'] = array();
    while (FALSE !== ($entry = readdir($handle))) {
      if (!in_array($entry, array(
        '.',
        '..',
        'default',
        'all',
        'example.sites.php',
        'README.txt',
      ))) {
        if (is_dir($drupal_root . '/sites/' . $entry)) {
          $this->registry['multisites'][] = $entry;
        }
      }
    }
    closedir($handle);
    if (!empty($this->registry['multisites'])) {
      return AuditCheck::AUDIT_CHECK_SCORE_FAIL;
    }
    return AuditCheck::AUDIT_CHECK_SCORE_PASS;
  }
}
