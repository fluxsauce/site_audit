<?php

namespace Drupal\site_audit\Plugin\SiteAuditCheck;

use Drupal\site_audit\Plugin\SiteAuditCheckBase;

/**
 * Provides the BestPracticesServices Check.
 *
 * @SiteAuditCheck(
 *  id = "best_practices_services",
 *  name = @Translation("sites/default/services.yml"),
 *  description = @Translation("Check if the services file exists."),
 *  report = "best_practices"
 * )
 */
class BestPracticesServices extends SiteAuditCheckBase {

  /**
   * {@inheritdoc}.
   */
  public function getResultFail() {
    return $this->t('services.yml does not exist! Copy the default.service.yml to services.yml and see https://www.drupal.org/documentation/install/settings-file for details.');
  }

  /**
   * {@inheritdoc}.
   */
  public function getResultInfo() {}

  /**
   * {@inheritdoc}.
   */
  public function getResultPass() {
    return $this->t('services.yml exists and is not a symbolic link.');
  }

  /**
   * {@inheritdoc}.
   */
  public function getResultWarn() {
    return $this->t('sites/default/services.yml is a symbolic link.');
  }

  /**
   * {@inheritdoc}.
   */
  public function getAction() {
    if ($this->score == SiteAuditCheckBase::AUDIT_CHECK_SCORE_WARN) {
      return $this->t('Don\'t rely on symbolic links for core configuration files; copy services.yml where it should be and remove the symbolic link.');
    }
    if ($this->score == SiteAuditCheckBase::AUDIT_CHECK_SCORE_FAIL) {
      return $this->t('Create services.yml file inside sites/default directory by copying default.services.yml file. See https://www.drupal.org/documentation/install/settings-file for details.');
    }
  }

  /**
   * {@inheritdoc}.
   */
  public function calculateScore() {
    if (file_exists(DRUPAL_ROOT . '/sites/default/services.yml')) {
      if (is_link(DRUPAL_ROOT . '/sites/default/services.yml')) {
        return SiteAuditCheckBase::AUDIT_CHECK_SCORE_WARN;
      }
      return SiteAuditCheckBase::AUDIT_CHECK_SCORE_PASS;
    }
    return SiteAuditCheckBase::AUDIT_CHECK_SCORE_FAIL;
  }

}
