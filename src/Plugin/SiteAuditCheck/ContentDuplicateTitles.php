<?php

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
    $ret_val = [
      '#theme' => 'table',
      '#header' => [
        $this->t('Content Type'),
        $this->t('Title'),
        $this->t('Count'),
      ],
      '#rows' => [],
    ];
    foreach ($this->registry->nodes_duplicate_titles as $content_type => $title_counts) {
      foreach ($title_counts as $title => $count) {
        $ret_val['#rows'][] = [
          $content_type,
          $title,
          $count,
        ];
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
      // This hasn't been checked, so check it// make sure we have entities
      // \Drupal\site_audit\Plugin\SiteAuditCheck\ContentEntityTypes::calculateScore();
      $this->checkInvokeCalculateScore('content_entity_types');
    }
    if (empty($this->registry->content_entity_type_counts)) {
      return SiteAuditCheckBase::AUDIT_CHECK_SCORE_INFO;
    }

    $query = \Drupal::database()->select('node_field_data', 'nfd');
    $query->addExpression('COUNT(nfd.title)', 'duplicate_count');
    $query->fields('nfd', ['title', 'type']);
    $query->groupBy('nfd.title');
    $query->groupBy('nfd.type');
    $query->having('COUNT(nfd.title) > 1');
    $query->orderBy('duplicate_count', 'DESC');

    $result = $query->execute();

    $this->registry->nodes_duplicate_titles = [];
    $this->registry->nodes_duplicate_title_count = 0;
    $content_types = $content_types = \Drupal::service('entity_type.bundle.info')->getBundleInfo("node");
    while ($row = $result->fetchAssoc()) {
      $label = $content_types[$row['type']]['label'];
      $title = Html::escape($row['title']);
      $this->registry->nodes_duplicate_titles[$label][$title] = $row['duplicate_count'];
      $this->registry->nodes_duplicate_title_count += $row['duplicate_count'];
    }

    if (!empty($this->registry->nodes_duplicate_titles)) {
      return SiteAuditCheckBase::AUDIT_CHECK_SCORE_WARN;
    }

    return SiteAuditCheckBase::AUDIT_CHECK_SCORE_PASS;
  }

}
