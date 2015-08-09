<?php
/**
 * @file
 * Contains \SiteAudit\Check\BestPractices\FolderStructure.
 */

/**
 * Class SiteAuditCheckBestPracticesFolderStructure.
 */
class SiteAuditCheckBestPracticesFolderStructure extends SiteAuditCheckAbstract {
  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Folder Structure');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Checks if modules/contrib and modules/custom directory is present');
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
    return dt('modules/contrib and modules/custom directories exist.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultWarn().
   */
  public function getResultWarn() {
    if (!$this->registry['contrib'] && !$this->registry['custom']) {
      return dT('Both modules/contrib and modules/custom directories are not present');
    }
    if (!$this->registry['contrib']) {
      return dt('modules/contrib directory is not present.');
    }
    if (!$this->registry['custom']) {
      return dt('modules/custom directory is not present');
    }
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getAction().
   */
  public function getAction() {
    if ($this->score == SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN) {
      return dt('Put all the contrib modules inside modules/contrib directory and custom modules inside modules/custom directory');
    }
  }

  /**
   * Implements \SiteAudit\Check\Abstract\calculateScore().
   */
  public function calculateScore() {
    $drupal_root = drush_get_context('DRUSH_SELECTED_DRUPAL_ROOT');
    $this->registry['contrib'] = is_dir($drupal_root . '/modules/contrib');
    $this->registry['custom'] = is_dir($drupal_root . '/modules/custom');
    if (!$this->registry['contrib'] || !$this->registry['custom']) {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN;
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS;
  }

}
