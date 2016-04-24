<?php
/**
 * @file
 * Contains \SiteAudit\Check\BestPractices\Multisite.
 */

/**
 * Class SiteAuditCheckBestPracticesMultisite.
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
  public function getResultWarn() {
    if ($this->registry['multisite_enabled']) {
      return dt('sites/sites.php is present but no multisite directories are present.');
    }
    else {
      return dt('Multisite directories are present but sites/sites.php is not present.');
    }
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getAction().
   */
  public function getAction() {
    if ($this->score == SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL) {
      return dt('See https://pantheon.io/blog/drupal-multisite-much-ado-about-drupal-multisite for details.');
    }
    if ($this->score == SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN) {
      if ($this->registry['multisite_enabled']) {
        return dt('See https://www.drupal.org/node/2297419 for details on how to use multisite feature in Drupal 8.');
      }
      else {
        return dt('Inside the sites/ directory, copy example.sites.php to sites.php to create the configuration. See https://www.drupal.org/node/2297419 for details.');
      }
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
      ))
      ) {
        if (is_dir($drupal_root . '/sites/' . $entry)) {
          $this->registry['multisites'][] = $entry;
        }
      }
    }
    closedir($handle);
    if ($this->registry['multisite_enabled']) {
      if (drush_get_option('vendor') == 'pantheon') {
        return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL;
      }
      if (!empty($this->registry['multisites'])) {
        return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO;
      }
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN;
    }
    elseif (!empty($this->registry['multisites'])) {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN;
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS;
  }

}
