<?php
/**
 * @file
 * Contains \SiteAudit\Check\Content\FieldInstances.
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
      $ret_val .= '<tr><th>Entity Type</th><th>Field Name</th><th>Bundle Name</th><th>Count</th></tr>';
      foreach ($this->registry['field_instance_counts'] as $entity_type => $fields) {
        foreach ($fields as $field_name => $bundles) {
          foreach ($bundles as $bundle_name => $count) {
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
        $ret_val .= "Bundle: $bundle_name";
        foreach ($entity_types as $entity_type => $fields) {
          $ret_val .= PHP_EOL;
          if (!drush_get_option('json')) {
            $ret_val .= str_repeat(' ', 6);
          }
          $ret_val .= "Entity Type: $entity_type";
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
    $this->registry['field_api_map'] = field_info_field_map();
    $this->registry['field_instance_counts'] = array();

    foreach ($this->registry['field_api_map'] as $field_name => $field) {
      foreach ($field['bundles'] as $entity_type => $bundle_names) {
        foreach ($bundle_names as $bundle_name) {
          $query = new EntityFieldQuery();
          $query
            ->entityCondition('entity_type', $entity_type)
            ->entityCondition('bundle', $bundle_name)
            ->fieldCondition($field_name)
            ->count();
          $field_count = $query->execute();
          $this->registry['field_instance_counts'][$bundle_name][$entity_type][$field_name] = $field_count;
        }
      }
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO;
  }
}
