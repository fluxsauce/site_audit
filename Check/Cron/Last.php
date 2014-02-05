<?php
/**
 * @file
 * Contains \SiteAudit\Check\Cron\Last.
 */

class SiteAuditCheckCronLast extends SiteAuditCheckAbstract {
  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Last run');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Time Cron last executed');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getResultInfo().
   */
  public function getResultInfo() {
    if ($this->registry['cron_last']) {
      return dt('Cron last ran at @date (@ago ago)', array(
        '@date' => date('r', $this->registry['cron_last']),
        '@ago' => format_interval(time() - $this->registry['cron_last']),
      ));
    }
    return dt('Cron has never run.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultPass().
   */
  public function getResultPass() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getResultWarn().
   */
  public function getResultWarn() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getAction().
   */
  public function getAction() {}

  /**
   * Implements \SiteAudit\Check\Abstract\calculateScore().
   */
  public function calculateScore() {
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO;
  }
}
