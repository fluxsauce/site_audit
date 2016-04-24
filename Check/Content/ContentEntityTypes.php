<?php
/**
 * @file
 * Contains \SiteAudit\Check\Content\ContentTypes.
 */

/**
 * Class SiteAuditCheckContentContentEntityTypes.
 */
class SiteAuditCheckContentContentEntityTypes extends SiteAuditCheckAbstract {

  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Content entity types');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Available content entity types and counts');
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

    if (empty($this->registry['content_entity_type_counts'])) {
      if (drush_get_option('detail')) {
        return dt('No entities exist.');
      }
      return $ret_val;
    }
    if (drush_get_option('html') == TRUE) {
      $ret_val .= '<table class="table table-condensed">';
      $ret_val .= '<thead><tr><th>' . dt('Content Entity') . '</th><th>' . dt('Bundle') . '</th><th>' . dt('Count') . '</th></tr></thead>';
      foreach ($this->registry['content_entity_type_counts'] as $entity_type => $bundles) {
        foreach ($bundles as $bundle => $count) {
          $ret_val .= "<tr><td>$entity_type</td><td>$bundle</td><td>$count</td></tr>";
        }
      }
      $ret_val .= '</table>';
    }
    else {
      $rows = 0;
      foreach ($this->registry['content_entity_type_counts'] as $entity_type => $bundles) {
        if ($rows++ > 0) {
          $ret_val .= PHP_EOL;
          if (!drush_get_option('json')) {
            $ret_val .= str_repeat(' ', 4);
          }
        }
        $ret_val .= dt('Entity: @entity_type, total: @total', array(
          '@entity_type' => $entity_type,
          '@total' => $this->registry['entity_count'][$entity_type],
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
   * Implements \SiteAudit\Check\Abstract\getResultPass().
   */
  public function getResultPass() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getResultWarn().
   */
  public function getResultWarn() {
    return $this->getResultInfo();
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getAction().
   */
  public function getAction() {}

  /**
   * Implements \SiteAudit\Check\Abstract\calculateScore().
   */
  public function calculateScore() {
    $entity_manager = \Drupal::entityManager();
    $all_bundles = $entity_manager->getAllBundleInfo();

    foreach ($all_bundles as $entity_type => $bundles) {
      $bundle_column_name = $entity_manager->getDefinition($entity_type)->getKey('bundle');
      $interfaces = class_implements($entity_manager->getDefinition($entity_type)->getClass());
      if ($bundle_column_name != FALSE && in_array("Drupal\\Core\\Entity\\ContentEntityInterface", $interfaces)) {
        $this->registry['entity_count'][$entity_type] = 0;
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

            $this->registry['content_entity_type_counts'][$entity_type][$label] = $field_count;
            $this->registry['entity_count'][$entity_type] += $field_count;
            if ($field_count == 0) {
              $this->registry['content_types_unused'][$entity_type][] = $info['label'];
            }
          }
        }
      }
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO;
  }

}
