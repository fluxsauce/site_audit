<?php

namespace Drupal\site_audit\Plugin\SiteAuditCheck;

use Drupal\site_audit\Plugin\SiteAuditCheckBase;

/**
 * Provides the ContentEntityTypes Check.
 *
 * @SiteAuditCheck(
 *  id = "content_entity_types",
 *  name = @Translation("Content entity types"),
 *  description = @Translation("Available content entity types and counts"),
 *  report = "content"
 * )
 */
class ContentEntityTypes extends SiteAuditCheckBase {

  /**
   * {@inheritdoc}.
   */
  public function getResultFail() {}

  /**
   * {@inheritdoc}.
   */
  public function getResultInfo() {
    $ret_val = '';

    if (empty($this->registry->content_entity_type_counts)) {
      if ($this->options['detail']) {
        return $this->t('No entities exist.');
      }
      return $ret_val;
    }

    $table_rows = [];
    foreach ($this->registry->content_entity_type_counts as $entity_type => $bundles) {
      foreach ($bundles as $bundle => $count) {
        $table_rows[] = [
          $entity_type,
          $bundle,
          $count,
        ];
      }
    }

    $header = [
      $this->t('Content Entity'),
      $this->t('Bundle'),
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
  public function getResultWarn() {
    return $this->getResultInfo();
  }

  /**
   * {@inheritdoc}.
   */
  public function getAction() {}

  /**
   * {@inheritdoc}.
   */
  public function calculateScore() {
    if (!isset($this)) {
      return SiteAuditCheckBase::AUDIT_CHECK_SCORE_INFO;
    }
    $all_bundles = \Drupal::service('entity_type.bundle.info')->getAllBundleInfo();
    // This might have already been run by Drupal\site_audit\Plugin\SiteAuditCheck\ContentEntityTypesUnused
    // if so we don't need to do it again.
    if (!isset($this->registry->content_entity_type_counts)) {
      $this->registry->content_types_unused = [];
      foreach ($all_bundles as $entity_type => $bundles) {
        $bundle_column_name = \Drupal::service('entity_type.manager')->getDefinition($entity_type)->getKey('bundle');
        $interfaces = class_implements(\Drupal::service('entity_type.manager')->getDefinition($entity_type)->getClass());
        if ($bundle_column_name != FALSE && in_array("Drupal\\Core\\Entity\\ContentEntityInterface", $interfaces)) {
          $this->registry->entity_count[$entity_type] = 0;
          foreach ($bundles as $bundle => $info) {
            if (get_class(\Drupal::service('entity_type.manager')->getStorage($entity_type)) != 'Drupal\Core\Entity\ContentEntityNullStorage') {
              $query = \Drupal::entityQuery($entity_type)
                ->condition($bundle_column_name, $bundle)
                ->count();
              $field_count = $query->execute();

              $label = $info['label'];
              if (is_object($label)) {
                $label = (string) $label;
              }

              $this->registry->content_entity_type_counts[$entity_type][$label] = $field_count;
              $this->registry->entity_count[$entity_type] += $field_count;
              if ($field_count == 0) {
                $this->registry->content_types_unused[$entity_type][] = $info['label'];
              }
            }
          }
        }
      }
    }

    return SiteAuditCheckBase::AUDIT_CHECK_SCORE_INFO;
  }

}
