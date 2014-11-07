<?php
/**
 * @file
 * Contains \SiteAudit\Check\Content\FieldsUnused.
 */

class SiteAuditCheckContentFieldsUnused extends SiteAuditCheckAbstract {
  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Unused fields');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Determine which fields are unused in each bundle.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getResultInfo().
   */
  public function getResultInfo() {
    return dt('There are no unused fields.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultPass().
   */
  public function getResultPass() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getResultWarn().
   */
  public function getResultWarn() {
    $report = array();
    foreach ($this->registry['fields_unused'] as $bundle_name => $fields) {
      $report[] = $bundle_name .= ': ' . implode(', ', $fields);
    }
    return dt('The following fields are unused: @report', array(
      '@report' => implode('; ', $report),
    ));
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getAction().
   */
  public function getAction() {
    if ($this->getScore() == SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN) {
      return dt('Consider removing unused fields.');
    }
  }

  /**
   * Implements \SiteAudit\Check\Abstract\calculateScore().
   */
  public function calculateScore() {
    $this->registry['fields_unused'] = array();

    foreach ($this->registry['field_instance_counts'] as $entity_type => $fields) {
      foreach ($fields as $field_name => $bundles) {
        foreach ($bundles as $bundle_name => $count) {
          if (!$count) {
            $this->registry['fields_unused'][$entity_type . '-' . $bundle_name][] = $field_name;
          }
        }
      }
    }

    if (!empty($this->registry['fields_unused'])) {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN;
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO;
  }
}
