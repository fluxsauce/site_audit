<?php
/**
 * @file
 * Contains \SiteAudit\Check\Content\Vocabularies.
 */

class SiteAuditCheckContentVocabularies extends SiteAuditCheckAbstract {
  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Taxonomy vocabularies');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Available vocabularies and term counts');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getResultInfo().
   */
  public function getResultInfo() {
    if (!isset($this->registry['vocabulary_counts'])) {
      return dt('The taxonomy module is not enabled.');
    }
    if (empty($this->registry['vocabulary_counts'])) {
      if (drush_get_option('detail')) {
        return dt('No vocabularies exist.');
      }
      return '';
    }
    $ret_val = '';
    if (drush_get_option('html') == TRUE) {
      $ret_val .= '<table class="table table-condensed">';
      $ret_val .= "<thead><tr><th>Vocabulary</th><th>Terms</th></tr></thead>";
      foreach ($this->registry['vocabulary_counts'] as $vocabulary => $count) {
        $ret_val .= "<tr><td>$vocabulary</td><td>$count</td></tr>";
      }
      $ret_val .= '</table>';
    }
    else {
      $ret_val  = 'Vocabulary: Count' . PHP_EOL;
      if (!drush_get_option('json')) {
        $ret_val .= str_repeat(' ', 4);
      }
      $ret_val .= '-------------------';
      foreach ($this->registry['vocabulary_counts'] as $vocabulary => $count) {
        $ret_val .= PHP_EOL;
        if (!drush_get_option('json')) {
          $ret_val .= str_repeat(' ', 4);
        }
        $ret_val .= $vocabulary . ': ' . $count;
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
    if (!module_exists('taxonomy')) {
      $this->abort = TRUE;
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO;
    }
    $sql_query  = 'SELECT COUNT(tid) AS count, {taxonomy_vocabulary}.name AS name ';
    $sql_query .= 'FROM {taxonomy_vocabulary} ';
    $sql_query .= 'LEFT JOIN {taxonomy_term_data} ON {taxonomy_vocabulary}.vid = {taxonomy_term_data}.vid ';
    $sql_query .= 'GROUP BY name ';
    $sql_query .= 'ORDER BY count DESC ';

    $result = db_query($sql_query);

    $this->registry['vocabulary_counts'] = $this->registry['vocabulary_unused'] = array();

    foreach ($result as $row) {
      if ($row->count == 0) {
        $this->registry['vocabulary_unused'][] = $row->name;
      }
      elseif (!drush_get_option('detail')) {
        continue;
      }
      $this->registry['vocabulary_counts'][$row->name] = $row->count;
    }

    // No need to check for unused vocabularies if there aren't any.
    if (empty($this->registry['vocabulary_counts'])) {
      $this->abort = TRUE;
    }

    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO;
  }
}
