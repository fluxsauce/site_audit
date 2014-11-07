<?php
/**
 * @file
 * Contains \SiteAudit\Check\Content\DuplicateTitles.
 */

class SiteAuditCheckContentDuplicateTitles extends SiteAuditCheckAbstract {
  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Duplicate titles');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Scan nodes for duplicate titles within a particular content type');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getResultInfo().
   */
  public function getResultInfo() {
    return dt('No nodes exist, which also means no duplicate titles.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultPass().
   */
  public function getResultPass() {
    return dt('No nodes with duplicate titles exist.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultWarn().
   */
  public function getResultWarn() {
    if (!drush_get_option('detail')) {
      return dt('There are @count duplicate titles in the following types: @types', array(
        '@count' => $this->registry['nodes_duplicate_title_count'],
        '@types' => implode(', ', array_keys($this->registry['nodes_duplicate_titles'])),
      ));
    }

    $ret_val = '';
    if (drush_get_option('html') == TRUE) {
      $ret_val .= '<table class="table table-condensed">';
      $ret_val .= "<thead><tr><th>Content Type</th><th>Title</th><th>Count</th></tr></thead>";
      foreach ($this->registry['nodes_duplicate_titles'] as $content_type => $title_counts) {
        foreach ($title_counts as $title => $count) {
          $ret_val .= "<tr><td>$content_type</td><td>$title</td><td>$count</td></tr>";
        }
      }
      $ret_val .= '</table>';
    }
    else {
      $ret_val  = 'Content Type: "Title" (Count)' . PHP_EOL;
      if (!drush_get_option('json')) {
        $ret_val .= str_repeat(' ', 4);
      }
      $ret_val .= '-----------------------------';
      foreach ($this->registry['nodes_duplicate_titles'] as $content_type => $title_counts) {
        foreach ($title_counts as $title => $count) {
          $ret_val .= PHP_EOL;
          if (!drush_get_option('json')) {
            $ret_val .= str_repeat(' ', 4);
          }
          $ret_val .= "$content_type: \"$title\" ($count)";
        }
      }
    }
    return $ret_val;

  }

  /**
   * Implements \SiteAudit\Check\Abstract\getAction().
   */
  public function getAction() {
    if ($this->getScore() == SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN) {
      return dt('Consider reviewing your content and finding a way to disambiguate the duplicate titles.');
    }
  }

  /**
   * Implements \SiteAudit\Check\Abstract\calculateScore().
   */
  public function calculateScore() {
    if (empty($this->registry['content_type_counts'])) {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO;
    }

    $sql_query  = 'SELECT title, type, COUNT(0) AS duplicate_count ';
    $sql_query .= 'FROM {node} ';
    $sql_query .= 'GROUP BY title, type ';
    $sql_query .= 'HAVING (duplicate_count > 1) ';
    $sql_query .= 'ORDER BY duplicate_count DESC, title ASC ';

    $result = db_query($sql_query);

    $this->registry['nodes_duplicate_titles'] = array();
    $this->registry['nodes_duplicate_title_count'] = 0;

    foreach ($result as $row) {
      $this->registry['nodes_duplicate_titles'][$row->type][check_plain($row->title)] = $row->duplicate_count;
      $this->registry['nodes_duplicate_title_count'] += $row->duplicate_count;
    }

    if (!empty($this->registry['nodes_duplicate_titles'])) {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN;
    }

    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS;
  }
}
