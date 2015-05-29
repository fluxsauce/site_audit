<?php
/**
 * @file
 * Contains \SiteAudit\Check\Content\ContentTypes.
 */

/**
 * Class SiteAuditCheckContentContentTypes.
 */
class SiteAuditCheckContentContentTypes extends SiteAuditCheckAbstract {
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

    if (empty($this->registry['content_type_counts'])) {
      if (drush_get_option('detail')) {
        return dt('No nodes exist.');
      }
      return $ret_val;
    }

    $ret_val .= dt("Total {$this->registry['node_count']} nodes");
    if (drush_get_option('html') == TRUE) {
      $ret_val = "<p>$ret_val</p>";
    }
    else {
      $ret_val .= PHP_EOL;
    }

    if (drush_get_option('html') == TRUE) {
      $ret_val .= '<table class="table table-condensed">';
      $ret_val .= "<thead><tr><th>" . dt("Content Type") . "</th><th>" . dt("Node Count") . "</th></tr></thead>";
      foreach ($this->registry['content_type_counts'] as $content_type => $count) {
        $ret_val .= "<tr><td>$content_type</td><td>$count</td></tr>";
      }
      $ret_val .= '</table>';
    }
    else {
      if (!drush_get_option('json')) {
        $ret_val .= str_repeat(' ', 4);
      }
      $ret_val .= '-------------------';
      foreach ($this->registry['content_type_counts'] as $content_type => $count) {
        $ret_val .= PHP_EOL;
        if (!drush_get_option('json')) {
          $ret_val .= str_repeat(' ', 4);
        }
        $ret_val .= $content_type . ': ' . $count;
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
    $content_types = \Drupal::entityManager()->getBundleInfo('node');
    $sql_query  = 'SELECT COUNT({node}.nid) AS count, {node}.type AS type ';
    $sql_query .= 'FROM {node} ';
    $sql_query .= 'GROUP BY type ';
    $sql_query .= 'ORDER BY count DESC ';

    $result = db_query($sql_query);

    $this->registry['content_type_counts'] = $this->registry['content_types_unused'] = array();
    $this->registry['node_count'] = 0;

    foreach ($result as $row) {
      $label = $content_types[$row->type]['label'];
      $this->registry['content_type_counts'][$label] = $row->count;
      $this->registry['node_count'] += $row->count;
    }
    foreach ($content_types as $type) {
      if (array_search($type['label'], array_keys($this->registry['content_type_counts'])) === FALSE) {
        $this->registry['content_types_unused'][] = $type['label'];
      }
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO;
  }

}
