<?php

namespace Drupal\site_audit\Plugin\SiteAuditCheck;

use Drupal\site_audit\Plugin\SiteAuditCheckBase;
use Drupal\Core\Logger\RfcLogLevel;

/**
 * Provides the WatchdogPhp Check.
 *
 * @SiteAuditCheck(
 *  id = "watchdog_php",
 *  name = @Translation("PHP messages"),
 *  description = @Translation("Count PHP notices, warnings and errors."),
 *  report = "watchdog",
 *  weight = 0,
 * )
 */
class WatchdogPhp extends SiteAuditCheckBase {

  /**
   * {@inheritdoc}.
   */
  public function getResultFail() {}

  /**
   * {@inheritdoc}.
   */
  public function getResultInfo() {
    $counts = [];
    foreach ($this->registry->php_counts as $severity => $count) {
      $counts[] = $severity . ': ' . $count;
    }
    $ret_val = implode(', ', $counts);
    $ret_val .= ' - total ' . $this->registry->percent_php . '%';
    return $ret_val;
  }

  /**
   * {@inheritdoc}.
   */
  public function getResultPass() {
    return $this->t('No PHP warnings, notices or errors.');
  }

  /**
   * {@inheritdoc}.
   */
  public function getResultWarn() {
    return $this->getResultInfo();
  }

  /**
   * {@inheritdoc}.
   */
  public function getAction() {
    if ($this->score == SiteAuditCheckBase::AUDIT_CHECK_SCORE_WARN) {
      return $this->t('Every time Drupal logs a PHP notice, warning or error, PHP executes slower and the writing operation locks the database. By eliminating the problems, your site will be faster.');
    }
  }

  /**
   * {@inheritdoc}.
   */
  public function calculateScore() {
    $this->registry->php_counts = [];
    $this->registry->php_count_total = 0;
    $this->registry->percent_php = 0;

    $query = \Drupal::database()->select('watchdog');
    $query->addExpression('COUNT(*)', 'count');
    $query->addField('watchdog', 'severity');
    $query->groupBy('severity');
    $query->orderBy('severity', 'ASC');
    $result = $query->execute();

    $severity_levels = $this->watchdog_severity_levels();
    while ($row = $result->fetchObject()) {
      $row->severity = $severity_levels[$row->severity];
      // $row = watchdog_format_result($result);
      if (!isset($this->registry->php_counts[$row->severity])) {
        $this->registry->php_counts[$row->severity] = 0;
      }
      $this->registry->php_counts[$row->severity]++;
      $this->registry->php_count_total++;
    }

    $this->registry->percent_php = round(($this->registry->php_count_total / $this->registry->count_entries) * 100, 2);
    if ($this->registry->percent_php >= 10) {
      return SiteAuditCheckBase::AUDIT_CHECK_SCORE_WARN;
    }
    return SiteAuditCheckBase::AUDIT_CHECK_SCORE_INFO;
  }

  /**
   * Watchdog severity levels.
   *
   * @see drush_watchdog_severity_levels()
   */
  public function watchdog_severity_levels() {
    return [
      RfcLogLevel::EMERGENCY => 'emergency',
      RfcLogLevel::ALERT => 'alert',
      RfcLogLevel::CRITICAL => 'critical',
      RfcLogLevel::ERROR => 'error',
      RfcLogLevel::WARNING => 'warning',
      RfcLogLevel::NOTICE => 'notice',
      RfcLogLevel::INFO => 'info',
      RfcLogLevel::DEBUG => 'debug',
    ];
  }

}
