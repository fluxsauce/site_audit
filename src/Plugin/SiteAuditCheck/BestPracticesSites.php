<?php

namespace Drupal\site_audit\Plugin\SiteAuditCheck;

use Drupal\site_audit\Plugin\SiteAuditCheckBase;

/**
 * Provides the BestPracticesSites Check.
 *
 * @SiteAuditCheck(
 *  id = "best_practices_sites",
 *  name = @Translation("sites/sites.php"),
 *  description = @Translation("Check if multisite configuration file is a symbolic link."),
 *  report = "best_practices"
 * )
 */
class BestPracticesSites extends SiteAuditCheckBase {

  /**
   * {@inheritdoc}.
   */
  public function getResultFail() {}

  /**
   * {@inheritdoc}.
   */
  public function getResultInfo() {}

  /**
   * {@inheritdoc}.
   */
  public function getResultPass() {
    if ($this->registry->multisite_enabled) {
      return $this->t('sites.php is not a symbolic link.');
    }
    else {
      return $this->t('sites.php does not exist.');
    }
  }

  /**
   * {@inheritdoc}.
   */
  public function getResultWarn() {
    return $this->t('sites/sites.php is a symbolic link.');
  }

  /**
   * {@inheritdoc}.
   */
  public function getAction() {
    if ($this->score == SiteAuditCheckBase::AUDIT_CHECK_SCORE_WARN) {
      return $this->t('Don\'t rely on symbolic links for core configuration files; copy sites.php where it should be and remove the symbolic link.');
    }
  }

  /**
   * {@inheritdoc}.
   */
  public function calculateScore() {
    $this->registry->multisite_enabled = file_exists(DRUPAL_ROOT . '/sites/sites.php');
    if ($this->registry->multisite_enabled && is_link(DRUPAL_ROOT . '/sites/sites.php')) {
      return SiteAuditCheckBase::AUDIT_CHECK_SCORE_WARN;
    }
    return SiteAuditCheckBase::AUDIT_CHECK_SCORE_PASS;
  }

}
