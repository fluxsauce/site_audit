<?php
/**
 * @file
 * Contains Drupal\site_audit\Plugin\SiteAuditCheck\ContentFieldEnabled
 */

namespace Drupal\site_audit\Plugin\SiteAuditCheck;

use Drupal\site_audit\Plugin\SiteAuditCheckBase;
use Drupal\site_audit\Renderer\Html;

/**
 * Provides the ContentFieldEnabled Check.
 *
 * @SiteAuditCheck(
 *  id = "content_field_enabled",
 *  name = @Translation("Field status"),
 *  description = @Translation("Check to see if enabled"),
 *  report = "content"
 * )
 */
class ContentFieldEnabled extends SiteAuditCheckBase {

  /**
   * {@inheritdoc}.
   */
  public function getResultFail() {}

  /**
   * {@inheritdoc}.
   */
  public function getResultInfo() {
     return $this->t('Field is not enabled.');
  }

  /**
   * {@inheritdoc}.
   */
  public function getResultPass() {
    return $this->t('Field is enabled.');
  }

  /**
   * {@inheritdoc}.
   */
  public function getResultWarn() {}

  /**
   * {@inheritdoc}.
   */
  public function getAction() {}

  /**
   * {@inheritdoc}.
   */
  public function calculateScore() {
    if (!\Drupal::moduleHandler()->moduleExists('field')) {
      $this->abort = TRUE;
      return SiteAuditCheckBase::AUDIT_CHECK_SCORE_INFO;
    }
    return SiteAuditCheckBase::AUDIT_CHECK_SCORE_PASS;
  }

}