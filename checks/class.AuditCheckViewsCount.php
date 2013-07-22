<?php
/**
 * @file
 * Contains \AuditCheckViewsCount.
 */

class AuditCheckViewsCount extends AuditCheck {
  /**
   * Implements \AuditCheck\getLabel().
   */
  public function getLabel() {
    return dt('Count');
  }

  /**
   * Implements \AuditCheck\getDescription().
   */
  public function getDescription() {
    return dt('Number of enabled Views.');
  }

  /**
   * Implements \AuditCheck\getResultFail().
   */
  public function getResultFail() {}

  /**
   * Implements \AuditCheck\getResultInfo().
   */
  public function getResultInfo() {}

  /**
   * Implements \AuditCheck\getResultPass().
   */
  public function getResultPass() {
    $views_count = count($this->registry['views']);
    if (!$views_count) {
      return dt('There are no enabled views.');
    }
    return dt('There are @count_views enabled views.', array(
      '@count_views' => count($this->registry['views']),
    ));
  }

  /**
   * Implements \AuditCheck\getResultWarning().
   */
  public function getResultWarning() {
    return $this->getResultPass();
  }

  /**
   * Implements \AuditCheck\getAction().
   */
  public function getAction() {
    if ($this->score == AuditCheck::AUDIT_CHECK_SCORE_WARN) {
      return dt('Consider disabling the views module if you don\'t need it.');
    }
  }

  /**
   * Implements \AuditCheck\getScore().
   */
  public function getScore() {
    $this->registry['views'] = array();

    foreach (views_get_all_views() as $view) {
      if ($view->disabled) {
        continue;
      }
      $this->registry['views'][] = $view;
    }

    if (empty($this->registry['views'])) {
      $this->abort = TRUE;
      return AuditCheck::AUDIT_CHECK_SCORE_WARN;
    }
    return AuditCheck::AUDIT_CHECK_SCORE_PASS;
  }
}
