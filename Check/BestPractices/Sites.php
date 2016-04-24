<?php
/**
 * @file
 * Contains \SiteAudit\Check\BestPractices\Sites.
 */

/**
 * Class SiteAuditCheckBestPracticesSites.
 */
class SiteAuditCheckBestPracticesSites extends SiteAuditCheckAbstract {

  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('sites/sites.php');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Check if multisite configuration file is a symbolic link.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getResultInfo().
   */
  public function getResultInfo() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getResultPass().
   */
  public function getResultPass() {
    if ($this->registry['multisite_enabled']) {
      return dt('sites.php is not a symbolic link.');
    }
    else {
      return dt('sites.php does not exist.');
    }
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultWarn().
   */
  public function getResultWarn() {
    return dt('sites/sites.php is a symbolic link.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getAction().
   */
  public function getAction() {
    if ($this->score == SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN) {
      return dt('Don\'t rely on symbolic links for core configuration files; copy sites.php where it should be and remove the symbolic link.');
    }
  }

  /**
   * Implements \SiteAudit\Check\Abstract\calculateScore().
   */
  public function calculateScore() {
    $drupal_root = drush_get_context('DRUSH_SELECTED_DRUPAL_ROOT');
    $this->registry['multisite_enabled'] = file_exists($drupal_root . '/sites/sites.php');
    if ($this->registry['multisite_enabled'] && is_link($drupal_root . '/sites/sites.php')) {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN;
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS;
  }

}
