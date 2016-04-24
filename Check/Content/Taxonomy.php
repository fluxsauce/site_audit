<?php
/**
 * @file
 * Contains \SiteAudit\Check\Content\ContentTypesUnused.
 */

/**
 * Class SiteAuditCheckContentTaxonomy.
 */
class SiteAuditCheckContentTaxonomy extends SiteAuditCheckAbstract {

  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Taxonomy status');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Check if Taxonomy module is enabled');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getResultInfo().
   */
  public function getResultInfo() {
    return dt('Taxonomy module is not enabled');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultPass().
   */
  public function getResultPass() {
    return dt('Taxonomy module is enabled');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultWarn().
   */
  public function getResultWarn() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getAction().
   */
  public function getAction() {}

  /**
   * Implements \SiteAudit\Check\Abstract\calculateScore().
   */
  public function calculateScore() {
    if (\Drupal::moduleHandler()->moduleExists('taxonomy')) {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS;
    }
    $this->abort = TRUE;
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO;
  }

}
