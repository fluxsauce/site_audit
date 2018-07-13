<?php
/**
 * @file
 * Contains Drupal\site_audit\Plugin\SiteAuditCheck\ContentTaxonomy
 */

namespace Drupal\site_audit\Plugin\SiteAuditCheck;

use Drupal\site_audit\Plugin\SiteAuditCheckBase;
use Drupal\site_audit\Renderer\Html;

/**
 * Provides the ContentTaxonomy Check.
 *
 * @SiteAuditCheck(
 *  id = "content_taxonomy",
 *  name = @Translation("Taxonomy status"),
 *  description = @Translation("Check if Taxonomy module is enabled"),
 *  report = "content"
 * )
 */
class ContentTaxonomy extends SiteAuditCheckBase {

  /**
   * {@inheritdoc}.
   */
  public function getResultFail() {}

  /**
   * {@inheritdoc}.
   */
  public function getResultInfo() {
     return $this->t('Taxonomy module is not enabled');
  }

  /**
   * {@inheritdoc}.
   */
  public function getResultPass() {
    return $this->t('Taxonomy module is enabled');
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
    if (\Drupal::moduleHandler()->moduleExists('taxonomy')) {
      return SiteAuditCheckBase::AUDIT_CHECK_SCORE_PASS;
    }
    $this->abort = TRUE;
    return SiteAuditCheckBase::AUDIT_CHECK_SCORE_INFO;
  }
}