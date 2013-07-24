<?php
/**
 * @file
 * Contains \SiteAudit\Check\Cron\Running.
 */

class SiteAuditCheckCronRunning extends SiteAuditCheckAbstract {
  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Running');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Check to see if cron is actively running.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getResultInfo().
   */
  public function getResultInfo() {
    if ($this->registry['cron_semaphore']) {
      return dt('Cron lock was set @cron_semaphore.', array(
        '@cron_semaphore' => date('r', $this->registry['cron_semaphore']),
      ));
    }
    return dt('Cron is not currently running.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultPass().
   */
  public function getResultPass() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getResultWarn().
   */
  public function getResultWarn() {
    return dt('Cron lock has been set for more than an hour and is most likely stuck.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getAction().
   */
  public function getAction() {
    if ($this->score == SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN) {
      return dt('If Cron is not currently running, delete variable cron_semaphore.');
    }
  }

  /**
   * Implements \SiteAudit\Check\Abstract\calculateScore().
   */
  public function calculateScore() {
    global $conf;
    $this->registry['cron_semaphore'] = variable_get('cron_semaphore');
    if ($this->registry['cron_semaphore'] && ((time() - $this->registry['cron_semaphore'] > 3600))) {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN;
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO;
  }
}
