<?php
/**
 * @file
 * Contains \SiteAudit\Check\BestPractices\Multisite.
 */

class SiteAuditCheckBestPracticesMultisite extends SiteAuditCheckAbstract {
  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Multisite');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Detect multisite configurations.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {
    return dt('The following multisite configuration(s) were detected: @list', array(
      '@list' => implode(', ', $this->registry['multisites']),
    ));
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultInfo().
   */
  public function getResultInfo() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getResultPass().
   */
  public function getResultPass() {
    return dt('No multisites detected.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultWarning().
   */
  public function getResultWarning() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getAction().
   */
  public function getAction() {
    if ($this->score == SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL) {
      return dt('See https://www.getpantheon.com/blog/much-ado-about-drupal-multisite for details.');
    }
  }

  /**
   * Implements \SiteAudit\Check\Abstract\calculateScore().
   */
  public function calculateScore() {
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
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL;
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS;
  }
}
