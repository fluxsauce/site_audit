<?php
/**
 * @file
 * Contains Drupal\site_audit\Plugin\SiteAuditCheck\BestPracticesSitesDefault
 */

namespace Drupal\site_audit\Plugin\SiteAuditCheck;

use Drupal\site_audit\Plugin\SiteAuditCheckBase;

/**
 * Provides the BestPracticesSitesDefault Check.
 *
 * @SiteAuditCheck(
 *  id = "best_practices_fast_404",
 *  name = @Translation("sites/default"),
 *  description = @Translation("Check if it exists and isn\'t symbolic"),
 *  report = "best_practices"
 * )
 */
class BestPracticesSitesDefault extends SiteAuditCheckBase {

  /**
   * {@inheritdoc}.
   */
  public function getResultFail() {
    return $this->t('sites/default does not exist!');
  }

  /**
   * {@inheritdoc}.
   */
  public function getResultInfo() {
    return $this->t('sites/default is a directory and not a symbolic link.');
  }

  /**
   * {@inheritdoc}.
   */
  public function getResultPass() {
    return $this->t('Fast 404 pages are enabled.');
  }

  /**
   * {@inheritdoc}.
   */
  public function getResultWarn() {
    return $this->t('sites/default exists as a symbolic link.');
  }

  /**
   * {@inheritdoc}.
   */
  public function getAction() {
    if ($this->score == SiteAuditCheckBase::AUDIT_CHECK_SCORE_FAIL) {
      return $this->t('sites/default is necessary; recreate the directory immediately.');
    }
    if ($this->score == SiteAuditCheckBase::AUDIT_CHECK_SCORE_WARN) {
      return $this->t('Avoid changing Drupal\'s site structure; remove the symbolic link and recreate sites/default.');
    }
  }

  /**
   * {@inheritdoc}.
   */
  public function calculateScore() {
    if (is_dir(DRUPAL_ROOT . '/sites/default')) {
      if (is_link(DRUPAL_ROOT . '/sites/default')) {
        return SiteAuditCheckBase::AUDIT_CHECK_SCORE_WARN;
      }
      return SiteAuditCheckBase::AUDIT_CHECK_SCORE_PASS;
    }
    return SiteAuditCheckBase::AUDIT_CHECK_SCORE_FAIL;
  }

}