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
    return dt('Multi-site');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Detect multi-site configurations.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {
    return dt('The following multi-site configuration(s) were detected: @list', array(
      '@list' => implode(', ', $this->registry['multisites']),
    ));
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultInfo().
   */
  public function getResultInfo() {
    return $this->getResultFail();
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultPass().
   */
  public function getResultPass() {
    return dt('No multi-sites detected.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultWarn().
   */
  public function getResultWarn() {}

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
        '.svn',
        '.DS_Store',
      ))) {
        if (is_dir($drupal_root . '/sites/' . $entry)) {
          $this->registry['multisites'][] = $entry;
        }
      }
    }
    closedir($handle);
    if (!empty($this->registry['multisites'])) {
      if (drush_get_option('vendor') == 'pantheon') {
        return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL;
      }
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO;
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS;
  }
}
