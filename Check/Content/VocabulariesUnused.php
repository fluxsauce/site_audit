<?php
/**
 * @file
 * Contains \SiteAudit\Check\Content\ContentVocabulariesUnused.
 */

class SiteAuditCheckContentVocabulariesUnused extends SiteAuditCheckAbstract {
  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Unused vocabularies');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Check for unused vocabularies');
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
    return dt('There are no unused vocabularies.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultWarn().
   */
  public function getResultWarn() {
    return dt('The following vocabularies are unused: @vocabularies_unused', array(
      '@vocabularies_unused' => implode(', ', $this->registry['vocabulary_unused']),
    ));
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getAction().
   */
  public function getAction() {
    if ($this->getScore() == SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN) {
      return dt('Consider removing unused vocabularies.');
    }
  }

  /**
   * Implements \SiteAudit\Check\Abstract\calculateScore().
   */
  public function calculateScore() {
    if (empty($this->registry['vocabulary_unused'])) {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS;
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN;
  }
}
