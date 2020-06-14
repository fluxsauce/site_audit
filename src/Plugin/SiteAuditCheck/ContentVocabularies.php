<?php

namespace Drupal\site_audit\Plugin\SiteAuditCheck;

use Drupal\site_audit\Plugin\SiteAuditCheckBase;

/**
 * Provides the ContentVocabularies Check.
 *
 * @SiteAuditCheck(
 *  id = "content_vocabularies",
 *  name = @Translation("Taxonomy vocabularies"),
 *  description = @Translation("Available vocabularies and term counts"),
 *  report = "content",
 *  weight = 6,
 * )
 */
class ContentVocabularies extends SiteAuditCheckBase {

  /**
   * {@inheritdoc}.
   */
  public function getResultFail() {}

  /**
   * {@inheritdoc}.
   */
  public function getResultInfo() {
    if (!isset($this->registry->vocabulary_counts)) {
      return $this->t('The taxonomy module is not enabled.');
    }
    if (empty($this->registry->vocabulary_counts)) {
      if ($this->options['detail']) {
        return $this->t('No vocabularies exist.');
      }
      return '';
    }
    $ret_val = '';

    $table_rows = [];
    foreach ($this->registry->vocabulary_counts as $vocabulary => $count) {
      $table_rows[] = [
        $vocabulary,
        $count,
      ];
    }

    $header = [
      $this->t('Vocabulary'),
      $this->t('Terms'),
    ];
    return [
      '#theme' => 'table',
      '#class' => 'table-condensed',
      '#header' => $header,
      '#rows' => $table_rows,
    ];
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
    if (!\Drupal::moduleHandler()->moduleExists('taxonomy')) {
      return SiteAuditCheckBase::AUDIT_CHECK_SCORE_INFO;
    }

    if (!isset($this->registry->vocabulary_unused)) {
      $this->registry->vocabulary_unused = [];

      $vocabularies = \Drupal::service('entity_type.bundle.info')->getBundleInfo("taxonomy_term");

      $query = \Drupal::database()->select('taxonomy_term_field_data');
      $query->addExpression('COUNT(tid)', 'count');
      $query->addField('taxonomy_term_field_data', 'vid');
      $query->orderBy('count', 'DESC');
      $query->groupBy('vid');
      $result = $query->execute();

      $this->registry->vocabulary_counts = $this->registry->vocabulary_unused = [];

      while ($row = $result->fetchAssoc()) {
        $label = $vocabularies[$row['vid']]['label'];
        $this->registry->vocabulary_counts[$label] = $row['count'];
      }

      // Check for unused vocabularies.
      foreach ($vocabularies as $vocabulary) {
        if (array_search($vocabulary['label'], array_keys($this->registry->vocabulary_counts)) === FALSE) {
          $this->registry->vocabulary_unused[] = $vocabulary['label'];
          $this->registry->vocabulary_counts[$vocabulary['label']] = 0;
        }
      }
      // No need to check for unused vocabularies if there aren't any.
      if (empty($this->registry->vocabulary_counts)) {
        $this->abort = TRUE;
      }
    }

    return SiteAuditCheckBase::AUDIT_CHECK_SCORE_INFO;
  }

}
