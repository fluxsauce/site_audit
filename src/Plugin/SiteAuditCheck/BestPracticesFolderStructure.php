<?php
/**
 * @file
 * Contains Drupal\site_audit\Plugin\SiteAuditCheck\BestPracticesFolderStructure
 */

namespace Drupal\site_audit\Plugin\SiteAuditCheck;

use Drupal\site_audit\Plugin\SiteAuditCheckBase;

/**
 * Provides the BestPracticesFolderStructure Check.
 *
 * @SiteAuditCheck(
 *  id = "best_practices_folder_structure",
 *  name = @Translation("Folder Structure"),
 *  description = @Translation("Checks if modules/contrib and modules/custom directory is present"),
 *  report = "best_practices"
 * )
 */
class BestPracticesFolderStructure extends SiteAuditCheckBase {

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
    return $this->t('modules/contrib and modules/custom directories exist.');
  }

  /**
   * {@inheritdoc}.
   */
  public function getResultWarn() {
    if (!$this->registry->contrib && !$this->registry->custom) {
      return $this->t('Neither modules/contrib nor modules/custom directories are present!');
    }
    if (!$this->registry->contrib) {
      return $this->t('modules/contrib directory is not present!');
    }
    if (!$this->registry->custom) {
      return $this->t('modules/custom directory is not present!');
    }
  }

  /**
   * {@inheritdoc}.
   */
  public function getAction() {
    $message = '';
    if ($this->score == SiteAuditCheckBase::AUDIT_CHECK_SCORE_WARN) {
      if (!$this->registry->contrib && !$this->registry['custom']) {
        $message .= $this->t('Put all the contrib modules inside the ./modules/contrib directory and custom modules inside the ./modules/custom directory.');
      }
      elseif (!$this->registry->contrib) {
        $message .= $this->t('Put all the contrib modules inside the ./modules/contrib directory.');
      }
      elseif (!$this->registry->custom) {
        $message .= $this->t('Put all the custom modules inside the ./modules/custom directory.');
      }
      return $message . ' ' . $this->t('Moving modules may cause errors, so refer to https://www.drupal.org/node/183681 for information on how to best proceed.');
    }
  }

  /**
   * {@inheritdoc}.
   */
  public function calculateScore() {
    $this->registry->contrib = is_dir(DRUPAL_ROOT . '/modules/contrib');
    $this->registry->custom = is_dir(DRUPAL_ROOT . '/modules/custom');
    if (!$this->registry->contrib || !$this->registry->custom) {
      return SiteAuditCheckBase::AUDIT_CHECK_SCORE_WARN;
    }
    return SiteAuditCheckBase::AUDIT_CHECK_SCORE_PASS;
  }

}