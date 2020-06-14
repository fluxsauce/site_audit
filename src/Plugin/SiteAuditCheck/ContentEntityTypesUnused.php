<?php

namespace Drupal\site_audit\Plugin\SiteAuditCheck;

use Drupal\site_audit\Plugin\SiteAuditCheckBase;

/**
 * Provides the ContentEntityTypesUnused Check.
 *
 * @SiteAuditCheck(
 *  id = "content_enity_types_unused",
 *  name = @Translation("Unused content entity types"),
 *  description = @Translation("Check for unused content entity types"),
 *  report = "content"
 * )
 */
class ContentEntityTypesUnused extends SiteAuditCheckBase {

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
    return $this->t('There are no unused content types.');
  }

  /**
   * {@inheritdoc}.
   */
  public function getResultWarn() {
    $report = [];
    foreach ($this->registry->content_types_unused as $entity_type => $bundle) {
      $report[] = $entity_type .= ': ' . implode(', ', $bundle);
    }
    return $this->t('The following content entity types are unused: @content_types_unused', [
      '@content_types_unused' => implode('; ', $report),
    ]);
  }

  /**
   * {@inheritdoc}.
   */
  public function getAction() {
    if ($this->getScore() == SiteAuditCheckBase::AUDIT_CHECK_SCORE_WARN) {
      return $this->t('Consider removing unused content types.');
    }
  }

  /**
   * {@inheritdoc}.
   */
  public function calculateScore() {
    if (!isset($this->registry->content_types_unused)) {
      // This hasn't been checked, so check it.
      $this->checkInvokeCalculateScore('content_entity_types');
    }
    if (empty($this->registry->content_types_unused)) {
      return SiteAuditCheckBase::AUDIT_CHECK_SCORE_PASS;
    }
    return SiteAuditCheckBase::AUDIT_CHECK_SCORE_WARN;
  }

}
