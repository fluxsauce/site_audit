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
 *  description = @Translation("For each bundle, entity and instance, get the count of populated fields"),
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
    //if (drush_get_option('html') == TRUE) {
    if (TRUE) {
      $ret_val .= '<table class="table table-condensed">';
      $ret_val .= '<tr><th>' . $this->t('Entity Type') . '</th><th>' . $this->t('Field Name') . '</th><th>' . $this->t('Bundle Name') . '</th><th>' . $this->t('Count') . '</th></tr>';
      foreach ($this->registry->field_instance_counts as $bundle_name => $entity_types) {
        foreach ($entity_types as $entity_type => $fields) {
          foreach ($fields as $field_name => $count) {
            $ret_val .= "<tr><td>$entity_type</td><td>$field_name</td><td>$bundle_name</td><td>$count</td></tr>";
          }
        }
      }
      $ret_val .= '</table>';
    }
    else {
      $rows = 0;
      foreach ($this->registry->field_instance_counts as $bundle_name => $entity_types) {
        if ($rows++ > 0) {
          $ret_val .= PHP_EOL;
          if (!drush_get_option('json')) {
            $ret_val .= str_repeat(' ', 4);
          }
        }
        $ret_val .= dt('Bundle:') . $bundle_name;
        foreach ($entity_types as $entity_type => $fields) {
          $ret_val .= PHP_EOL;
          if (!drush_get_option('json')) {
            $ret_val .= str_repeat(' ', 6);
          }
          $ret_val .= dt('Entity Type:') . $entity_type;
          foreach ($fields as $field_name => $count) {
            $ret_val .= PHP_EOL;
            if (!drush_get_option('json')) {
              $ret_val .= str_repeat(' ', 8);
            }
            $ret_val .= "$field_name: $count";
          }
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
    $entity_manager = \Drupal::entityManager();
    $map = $entity_manager->getFieldMap();
    $this->registry->field_instance_counts = array();
    foreach ($map as $entity => $fields) {
      $bundle_column_name = $entity_manager->getDefinition($entity)->getKey('bundle');
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