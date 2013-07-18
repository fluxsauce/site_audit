<?php

class AuditCheckWatchdogPhp extends AuditCheck {
  public function getLabel() {
    return dt('PHP messages');
  }

  public function getResultFail() {}

  public function getResultInfo() {
    $counts = array();
    foreach ($this->registry['php_counts'] as $severity => $count) {
      $counts[] = $severity . ': ' . $count;
    }
    return implode(', ', $counts);
  }

  public function getResultPass() {
    return dt('No PHP warnings, notices or errors.');
  }

  public function getResultWarning() {
    return $this->getResultInfo();
  }

  public function getAction() {
    if ($this->score == AuditCheck::AUDIT_CHECK_SCORE_WARN) {
      return dt('Every time Drupal logs a PHP notice, warning or error, PHP executes slower and the writing operation locks the database. By eliminating the problems, your site will be faster.');
    }
  }

  public function getDescription() {
    return dt('Count PHP notices, warnings and errors.');
  }

  public function getScore() {
    $where = core_watchdog_query('php', NULL, NULL);
    $this->registry['php_counts'] = array();
    $this->registry['php_count_total'] = 0;
    $rsc = drush_db_select('watchdog', '*', $where['where'], $where['args'], 0, NULL, 'wid', 'DESC');
    while ($result = drush_db_fetch_object($rsc)) {
      $row = core_watchdog_format_result($result);
      if (!isset($counts[$row->severity])) {
        $this->registry['php_counts'][$row->severity] = 0;
      }
      $this->registry['php_counts'][$row->severity]++;
      $this->registry['php_count_total']++;
    }

    $this->registry['percent_php'] = 0;

    if (!$this->registry['php_count_total']) {
      $this->abort = TRUE;
      return AuditCheck::AUDIT_CHECK_SCORE_PASS;
    }

    $this->registry['percent_php'] = round(($this->registry['php_count_total'] / $this->registry['count_entries']) * 100);
    if ($this->registry['percent_php'] >= 10) {
      return AuditCheck::AUDIT_CHECK_SCORE_WARN;
    }
    return AuditCheck::AUDIT_CHECK_SCORE_INFO;
  }
}
