<?php
/**
 * @file
 * Contains \SiteAudit\Check\Content\FieldInstances.
 */

/**
 * Class SiteAuditCheckContentFieldInstances.
 */
class SiteAuditCheckContentFieldInstances extends SiteAuditCheckAbstract {

  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Field instance counts');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('For each bundle, entity and instance, get the count of populated fields');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getResultInfo().
   */
  public function getResultInfo() {
    $ret_val = '';
    if (drush_get_option('html') == TRUE) {
      $ret_val .= '<table class="table table-condensed">';
      $ret_val .= '<tr><th>' . dt('Entity Type') . '</th><th>' . dt('Field Name') . '</th><th>' . dt('Bundle Name') . '</th><th>' . dt('Count') . '</th></tr>';
      foreach ($this->registry['field_instance_counts'] as $bundle_name => $entity_types) {
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
      foreach ($this->registry['field_instance_counts'] as $bundle_name => $entity_types) {
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
   * Implements \SiteAudit\Check\Abstract\getResultPass().
   */
  public function getResultPass() {}

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
    $entity_manager = \Drupal::entityManager();
    $map = $entity_manager->getFieldMap();
    $this->registry['field_instance_counts'] = array();
    foreach ($map as $entity => $fields) {
      $bundle_column_name = $entity_manager->getDefinition($entity)->getKey('bundle');
      foreach ($fields as $field => $description) {
        if (!in_array($field, array_keys($this->registry['fields']))) {
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
          $this->registry['field_instance_counts'][$bundle][$entity][$field] = $field_count;
        }
      }
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO;
  }

}
