<?php
/**
 * @file
 * Contains \SiteAudit\Check\BestPractices\Service.
 */

/**
 * Class SiteAuditCheckBestPracticesServices.
 */
class SiteAuditCheckBestPracticesServices extends SiteAuditCheckAbstract {

  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('sites/default/services.yml');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Check if the services file exists.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {
    return dt('services.yml does not exist! Copy the default.service.yml to services.yml and see https://www.drupal.org/documentation/install/settings-file for details.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultInfo().
   */
  public function getResultInfo() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getResultPass().
   */
  public function getResultPass() {
    return dt('services.yml exists and is not a symbolic link.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultWarn().
   */
  public function getResultWarn() {
    return dt('sites/default/services.yml is a symbolic link.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getAction().
   */
  public function getAction() {
    if ($this->score == SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN) {
      return dt('Don\'t rely on symbolic links for core configuration files; copy services.yml where it should be and remove the symbolic link.');
    }
    if ($this->score == SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL) {
      return dt('Create services.yml file inside sites/default directory by copying default.services.yml file. See https://www.drupal.org/documentation/install/settings-file for details.');
    }
  }

  /**
   * Implements \SiteAudit\Check\Abstract\calculateScore().
   */
  public function calculateScore() {
    $drupal_root = drush_get_context('DRUSH_SELECTED_DRUPAL_ROOT');
    if (file_exists($drupal_root . '/sites/default/services.yml')) {
      if (is_link($drupal_root . '/sites/default/services.yml')) {
        return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN;
      }
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS;
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL;
  }

}
