<?php
/**
 * @file
 * Contains Drupal\site_audit\Plugin\SiteAuditCheck\ContentFieldInstances
 */

namespace Drupal\site_audit\Plugin\SiteAuditCheck;

use Drupal\site_audit\Plugin\SiteAuditCheckBase;
use Drupal\site_audit\Renderer\Html;

/**
 * Provides the ContentFieldInstances Check.
 *
 * @SiteAuditCheck(
 *  id = "content_field_instances",
 *  name = @Translation("Field instance counts"),
 *  description = @Translation("For each bundle, entity and instance, get the count of populated fields."),
 *  report = "content"
 * )
 */
class ContentFieldInstances extends SiteAuditCheckBase {

  /**
   * {@inheritdoc}.
   */
  public function getResultFail() {}

  /**
   * {@inheritdoc}.
   */
  public function getResultInfo() {
    $ret_val = '';

    $table_rows = [];
    foreach ($this->registry->field_instance_counts as $bundle_name => $entity_types) {
      foreach ($entity_types as $entity_type => $fields) {
        foreach ($fields as $field_name => $count) {
          $table_rows[] = [
            $entity_type,
            $field_name,
            $bundle_name,
            $count,
          ];
        }
      }
    }

    $header = [
      $this->t('Entity Type'),
      $this->t('Field Name'),
      $this->t('Bundle Name'),
      $this->t('Count'),
    ];
    return [
      '#theme' => 'table',
      '#class' => 'table-condensed',
      '#header' => $header,
      '#rows' => $table_rows,
    ];
  }

  /**
   * {@inheritdoc}.
   */
  public function getResultPass() {}

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
    if (!isset($this->registry->fields)) {
      // we need to calculate, so call the class that does it
      $this->checkInvokeCalculateScore('content_field_count');
    }
    $map = \Drupal::service('entity_field.manager')->getFieldMap();
    $this->registry->field_instance_counts = array();
    foreach ($map as $entity => $fields) {
      $bundle_column_name = \Drupal::service('entity_type.manager')->getDefinition($entity)->getKey('bundle');
      foreach ($fields as $field => $description) {
        if (!in_array($field, array_keys($this->registry->fields))) {
          continue;
        }
        foreach ($description['bundles'] as $bundle) {
          $query = \Drupal::entityQuery($entity);
          if (!empty($bundle_column_name)) {
            $query->condition($bundle_column_name, $bundle);
          }
          $query->exists($field)
            ->count();
          $field_count = $query->execute();
          $this->registry->field_instance_counts[$bundle][$entity][$field] = $field_count;
        }
      }
    }
    return SiteAuditCheckBase::AUDIT_CHECK_SCORE_INFO;
  }
}
