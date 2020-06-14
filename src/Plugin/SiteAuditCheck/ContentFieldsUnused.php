<?php

namespace Drupal\site_audit\Plugin\SiteAuditCheck;

use Drupal\site_audit\Plugin\SiteAuditCheckBase;

/**
 * Provides the ContentFieldsUnused Check.
 *
 * @SiteAuditCheck(
 *  id = "content_field_unused",
 *  name = @Translation("Unused fields"),
 *  description = @Translation("Determine which fields are unused in each bundle."),
 *  report = "content"
 * )
 */
class ContentFieldsUnused extends SiteAuditCheckBase {

  /**
   * {@inheritdoc}.
   */
  public function getResultFail() {}

  /**
   * {@inheritdoc}.
   */
  public function getResultInfo() {
    return $this->t('There are no unused fields.');
  }

  /**
   * {@inheritdoc}.
   */
  public function getResultPass() {}

  /**
   * {@inheritdoc}.
   */
  public function getResultWarn() {
    $report = [];
    foreach ($this->registry->fields_unused as $bundle_name => $fields) {
      $report[] = $bundle_name .= ': ' . implode(', ', $fields);
    }
    return $this->t('The following fields are unused: @report', [
      '@report' => implode('; ', $report),
    ]);
  }

  /**
   * {@inheritdoc}.
   */
  public function getAction() {
    if ($this->getScore() == SiteAuditCheckBase::AUDIT_CHECK_SCORE_WARN) {
      return $this->t('Consider removing unused fields.');
    }
  }

  /**
   * {@inheritdoc}.
   */
  public function calculateScore() {
    $this->registry->fields_unused = [];

    foreach ($this->registry->field_instance_counts as $bundle_name => $entity_types) {
      foreach ($entity_types as $entity_type => $fields) {
        foreach ($fields as $field_name => $count) {
          if (!$count) {
            $this->registry->fields_unused[$entity_type . '-' . $bundle_name][] = $field_name;
          }
        }
      }
    }

    if (!empty($this->registry->fields_unused)) {
      return SiteAuditCheckBase::AUDIT_CHECK_SCORE_WARN;
    }
    return SiteAuditCheckBase::AUDIT_CHECK_SCORE_INFO;
  }

}
