<?php

class AuditCheckViewsCount extends AuditCheck {
  public function getLabel() {
    return dt('Count');
  }

  public function getResultFail() {}

  public function getResultInfo() {}

  public function getResultPass() {
    $views_count = count($this->registry['views']);
    if (!$views_count) {
      return dt('There are no enabled views.');
    }
    return dt('There are @count_views enabled views.', array(
      '@count_views' => count($this->registry['views']),
    ));
  }

  public function getResultWarning() {
    return $this->getResultPass();
  }

  public function getAction() {
    if ($this->score == AuditCheck::AUDIT_CHECK_SCORE_WARN) {
      return dt('Consider disabling the views module if you don\'t need it.');
    }
  }

  public function getDescription() {
    return dt('Number of enabled Views.');
  }

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
