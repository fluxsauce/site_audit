<?php
/**
 * @file
 * Contains Drupal\site_audit\Plugin\SiteAuditCheck\ContentDuplicateTitles
 */

namespace Drupal\site_audit\Plugin\SiteAuditCheck;

use Drupal\site_audit\Plugin\SiteAuditCheckBase;
use Drupal\site_audit\Renderer\Html;

/**
 * Provides the ContentDuplicateTitles Check.
 *
 * @SiteAuditCheck(
 *  id = "content_duplicate_titles",
 *  name = @Translation("Duplicate titles"),
 *  description = @Translation("Scan nodes for duplicate titles within a particular content type"),
 *  report = "content"
 * )
 */
class ContentDuplicateTitles extends SiteAuditCheckBase {

  /**
   * {@inheritdoc}.
   */
  public function getResultFail() {}

  /**
   * {@inheritdoc}.
   */
  public function getResultInfo() {
    return $this->t('No nodes exist, which also means no duplicate titles.');
  }

  /**
   * {@inheritdoc}.
   */
  public function getResultPass() {
    return $this->t('No nodes with duplicate titles exist.');
  }

  /**
   * {@inheritdoc}.
   */
  public function getResultWarn() {
    /*
    if (!drush_get_option('detail')) {
      return dt('There are @count duplicate titles in the following types: @types', array(
        '@count' => $this->registry['nodes_duplicate_title_count'],
        '@types' => implode(', ', array_keys($this->registry['nodes_duplicate_titles'])),
      ));
    }
    */

    $ret_val = '';
    //if (drush_get_option('html') == TRUE) {
    if (TRUE) {
      $ret_val .= '<table class="table table-condensed">';
      $ret_val .= '<thead><tr><th>' . $this->t('Content Type') . '</th><th>' . $this->t('Title') . '</th><th>' . $this->t('Count') . '</th></tr></thead>';
      foreach ($this->registry->nodes_duplicate_titles as $content_type => $title_counts) {
        foreach ($title_counts as $title => $count) {
          $ret_val .= "<tr><td>$content_type</td><td>$title</td><td>$count</td></tr>";
        }
      }
      $ret_val .= '</table>';
    }
    else {
      $ret_val  = dt('Content Type: "Title" (Count)') . PHP_EOL;
      if (!drush_get_option('json')) {
        $ret_val .= str_repeat(' ', 4);
      }
      $ret_val .= '-----------------------------';
      foreach ($this->registry->nodes_duplicate_titles as $content_type => $title_counts) {
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
   * {@inheritdoc}.
   */
  public function getAction() {
    if ($this->getScore() == SiteAuditCheckBase::AUDIT_CHECK_SCORE_WARN) {
      return $this->t('Consider reviewing your content and finding a way to disambiguate the duplicate titles.');
    }
  }

  /**
   * {@inheritdoc}.
   */
  public function calculateScore() {
    if (!isset($this->registry->content_entity_type_counts)) {
      /// this hasn't been checked, so check it// make sure we have entities
      \Drupal\site_audit\Plugin\SiteAuditCheck\ContentEntityTypes::calculateScore();
    }
    if (empty($this->registry->content_entity_type_counts)) {
      return SiteAuditCheckBase::AUDIT_CHECK_SCORE_INFO;
    }

    $query = db_select('node_field_data', 'nfd');
    $query->addExpression('COUNT(nfd.title)', 'duplicate_count');
    $query->fields('nfd', array('title', 'type'));
    $query->groupBy('nfd.title');
    $query->groupBy('nfd.type');
    $query->having('COUNT(nfd.title) > 1');
    $query->orderBy('duplicate_count', 'DESC');

    $result = $query->execute();

    $this->registry->nodes_duplicate_titles = array();
    $this->registry->nodes_duplicate_title_count = 0;
    $content_types = $content_types = \Drupal::entityManager()->getBundleInfo("node");
    while ($row = $result->fetchAssoc()) {
      $label = $content_types[$row['type']]['label'];
      $title = Html::escape($row['title']);
      $this->registry->nodes_duplicate_titles[$label][$title] = $row['duplicate_count'];
      $this->registry->nodes_duplicate_title_count += $row['duplicate_coun'];
    }


    if (!empty($this->registry->nodes_duplicate_titles)) {
      return SiteAuditCheckBase::AUDIT_CHECK_SCORE_WARN;
    }

    return SiteAuditCheckBase::AUDIT_CHECK_SCORE_PASS;
  }

}