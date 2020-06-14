<?php

namespace Drupal\site_audit\Plugin\SiteAuditCheck;

use Drupal\site_audit\Plugin\SiteAuditCheckBase;

/**
 * Provides the ContentVocabulariesUnused Check.
 *
 * @SiteAuditCheck(
 *  id = "content_vocabularies_unused",
 *  name = @Translation("Unused vocabularies"),
 *  description = @Translation("Check for unused vocabularies"),
 *  report = "content",
 *  weight = 7,
 * )
 */
class ContentVocabulariesUnused extends SiteAuditCheckBase {

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
    return $this->t('There are no unused vocabularies.');
  }

  /**
   * {@inheritdoc}.
   */
  public function getResultWarn() {
    return $this->t('The following vocabularies are unused: @vocabularies_unused', [
      '@vocabularies_unused' => implode(', ', $this->registry->vocabulary_unused),
    ]);
  }

  /**
   * {@inheritdoc}.
   */
  public function getAction() {
    if ($this->getScore() == SiteAuditCheckBase::AUDIT_CHECK_SCORE_WARN) {
      return $this->t('Consider removing unused vocabularies.');
    }
  }

  /**
   * {@inheritdoc}.
   */
  public function calculateScore() {
    if (!isset($this->registry->vocabulary_unused)) {
      $this->checkInvokeCalculateScore('content_vocabularies');
    }
    if (empty($this->registry->vocabulary_unused)) {
      return SiteAuditCheckBase::AUDIT_CHECK_SCORE_PASS;
    }
    return SiteAuditCheckBase::AUDIT_CHECK_SCORE_WARN;
  }

}
