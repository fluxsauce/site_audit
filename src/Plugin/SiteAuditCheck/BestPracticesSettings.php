<?php

namespace Drupal\site_audit\Plugin\SiteAuditCheck;

use Drupal\site_audit\Plugin\SiteAuditCheckBase;

/**
 * Provides the BestPracticesSettings Check.
 *
 * @SiteAuditCheck(
 *  id = "best_practices_settings",
 *  name = @Translation("sites/default/settings.php"),
 *  description = @Translation("Check if the configuration file exists."),
 *  report = "best_practices"
 * )
 */
class BestPracticesSettings extends SiteAuditCheckBase {

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
    return $this->t('settings.php exists and is not a symbolic link.');
  }

  /**
   * {@inheritdoc}.
   */
  public function getResultWarn() {
    return $this->t('sites/default/settings.php is a symbolic link.');
  }

  /**
   * {@inheritdoc}.
   */
  public function getAction() {
    if ($this->score == SiteAuditCheckBase::AUDIT_CHECK_SCORE_WARN) {
      return $this->t('Don\'t rely on symbolic links for core configuration files; copy settings.php where it should be and remove the symbolic link.');
    }
    if ($this->score == SiteAuditCheckBase::AUDIT_CHECK_SCORE_FAIL) {
      return $this->t('Even if environment settings are injected, create a stub settings.php file for compatibility.');
    }
  }

  /**
   * {@inheritdoc}.
   */
  public function calculateScore() {
    if (file_exists(DRUPAL_ROOT . '/sites/default/settings.php')) {
      if (is_link(DRUPAL_ROOT . '/sites/default/settings.php')) {
        return SiteAuditCheckBase::AUDIT_CHECK_SCORE_WARN;
      }
      return SiteAuditCheckBase::AUDIT_CHECK_SCORE_PASS;
    }
    return SiteAuditCheckBase::AUDIT_CHECK_SCORE_FAIL;
  }

}
