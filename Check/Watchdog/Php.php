<?php
/**
 * @file
 * Contains \SiteAudit\Check\Watchdog\Php.
 */

class SiteAuditCheckWatchdogPhp extends SiteAuditCheckAbstract {
  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('PHP messages');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Count PHP notices, warnings and errors.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getResultInfo().
   */
  public function getResultInfo() {
    $counts = array();
    foreach ($this->registry['php_counts'] as $severity => $count) {
      $counts[] = $severity . ': ' . $count;
    }
    $ret_val = implode(', ', $counts);
    $ret_val .= ' - total ' . $this->registry['percent_php'] . '%';
    return $ret_val;
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultPass().
   */
  public function getResultPass() {
    return dt('No PHP warnings, notices or errors.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultWarn().
   */
  public function getResultWarn() {
    return $this->getResultInfo();
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getAction().
   */
  public function getAction() {
    if ($this->score == SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN) {
      return dt('Every time Drupal logs a PHP notice, warning or error, PHP executes slower and the writing operation locks the database. By eliminating the problems, your site will be faster.');
    }
  }

  /**
   * Implements \SiteAudit\Check\Abstract\calculateScore().
   */
  public function calculateScore() {
    $where = core_watchdog_query('php', NULL, NULL);
    $this->registry['php_counts'] = array();
    $this->registry['php_count_total'] = 0;
    $rsc = drush_db_select('watchdog', '*', $where['where'], $where['args'], 0, NULL, 'wid', 'DESC');
    while ($result = drush_db_fetch_object($rsc)) {
      $row = core_watchdog_format_result($result);
      if (!isset($this->registry['php_counts'][$row->severity])) {
        $this->registry['php_counts'][$row->severity] = 0;
      }
      $this->registry['php_counts'][$row->severity]++;
      $this->registry['php_count_total']++;
    }

    $this->registry['percent_php'] = 0;

    if (!$this->registry['php_count_total']) {
      $this->abort = TRUE;
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS;
    }

    $this->registry['percent_php'] = round(($this->registry['php_count_total'] / $this->registry['count_entries']) * 100, 2);
    if ($this->registry['percent_php'] >= 10) {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN;
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO;
  }
}
