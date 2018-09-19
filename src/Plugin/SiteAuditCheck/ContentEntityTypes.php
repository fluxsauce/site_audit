<?php
/**
 * @file
 * Contains Drupal\site_audit\Plugin\SiteAuditCheck\ContentEntityTypes
 */

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
      if (drush_get_option('detail')) {
        return dt('No entities exist.');
      }
      return $ret_val;
    }
    //if (drush_get_option('html') == TRUE) {
    if (TRUE) {
      $ret_val .= '<table class="table table-condensed">';
      $ret_val .= '<thead><tr><th>' . $this->t('Content Entity') . '</th><th>' . $this->t('Bundle') . '</th><th>' . $this->t('Count') . '</th></tr></thead>';
      foreach ($this->registry->content_entity_type_counts as $entity_type => $bundles) {
        foreach ($bundles as $bundle => $count) {
          $ret_val .= "<tr><td>$entity_type</td><td>$bundle</td><td>$count</td></tr>";
        }
      }
      $ret_val .= '</table>';
    }
    else {
      $rows = 0;
      foreach ($this->registry->content_entity_type_counts as $entity_type => $bundles) {
        if ($rows++ > 0) {
          $ret_val .= PHP_EOL;
          if (!drush_get_option('json')) {
            $ret_val .= str_repeat(' ', 4);
          }
        }
        $ret_val .= $this->t('Entity: @entity_type, total: @total', array(
          '@entity_type' => $entity_type,
          '@total' => $this->registry->entity_count[$entity_type],
        ));
        foreach ($bundles as $bundle => $count) {
          $ret_val .= PHP_EOL;
          if (!drush_get_option('json')) {
            $ret_val .= str_repeat(' ', 6);
          }
          $ret_val .= "$bundle: $count";
        }
      }
    }
    return $ret_val;
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
    $entity_manager = \Drupal::entityManager();
    $all_bundles = $entity_manager->getAllBundleInfo();
    // this might have already been run by Drupal\site_audit\Plugin\SiteAuditCheck\ContentEntityTypesUnused
    // if so we don't need to do it again
    if (!isset($this->registry->content_entity_type_counts)) {
      $this->registry->content_types_unused = [];
      foreach ($all_bundles as $entity_type => $bundles) {
        $bundle_column_name = $entity_manager->getDefinition($entity_type)->getKey('bundle');
        $interfaces = class_implements($entity_manager->getDefinition($entity_type)->getClass());
        if ($bundle_column_name != FALSE && in_array("Drupal\\Core\\Entity\\ContentEntityInterface", $interfaces)) {
          $this->registry->entity_count[$entity_type] = 0;
          foreach ($bundles as $bundle => $info) {
            if (get_class($entity_manager->getStorage($entity_type)) != 'Drupal\Core\Entity\ContentEntityNullStorage') {
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