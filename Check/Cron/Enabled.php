<?php
/**
 * @file
 * Contains \SiteAudit\Check\Cron\Enabled.
 */

class SiteAuditCheckCronEnabled extends SiteAuditCheckAbstract {
  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Enabled');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Check to see if cron is scheduled to run.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {
    return dt('You have disabled cron, which will prevent routine system tasks from executing.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultInfo().
   */
  public function getResultInfo() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getResultPass().
   */
  public function getResultPass() {
    return dt('Cron is set to run every @minutes minutes.', array(
      '@minutes' => round($this->registry['cron_safe_threshold'] / 60),
    ));
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultWarn().
   */
  public function getResultWarn() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getAction().
   */
  public function getAction() {
    if ($this->score == SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL) {
      return dt('Please visit /admin/config/system/cron and set the cron frequency to something other than Never.');
    }
  }

  /**
   * Implements \SiteAudit\Check\Abstract\calculateScore().
   */
  public function calculateScore() {
    // Manually getting from database due to cron_safe_threshold known issue
    // https://drupal.org/node/1811224 in drush core.
    $sql_query  = 'SELECT value ';
    $sql_query .= 'FROM {variable} ';
    $sql_query .= 'WHERE name=:name';
    $cron_safe_threshold = db_query($sql_query, array(
      ':name' => 'cron_safe_threshold',
    ))->fetchField();
    // Unset; use default.
    if ($cron_safe_threshold === FALSE) {
      $cron_safe_threshold = DRUPAL_CRON_DEFAULT_THRESHOLD;
    }
    else {
      $cron_safe_threshold = unserialize($cron_safe_threshold);
    }
    $this->registry['cron_safe_threshold'] = $cron_safe_threshold;
    if (!$this->registry['cron_safe_threshold']) {
      $this->abort = TRUE;
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL;
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS;
  }
}
