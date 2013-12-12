<?php
/**
 * @file
 * Contains \SiteAudit\Check\Watchdog\Syslog.
 */

class SiteAuditCheckWatchdogSyslog extends SiteAuditCheckAbstract {
  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('syslog status');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Check to see if syslog logging is enabled');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {
    return dt('Syslog logging is enabled!');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultInfo().
   */
  public function getResultInfo() {
    if ($this->registry['syslog_enabled']) {
      return dt('Syslog logging is enabled.');
    }
    return dt('Syslog logging is not enabled.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultPass().
   */
  public function getResultPass() {
    return $this->getResultInfo();
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultWarn().
   */
  public function getResultWarn() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getAction().
   */
  public function getAction() {
    if ($this->getScore() == SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL && drush_get_option('vendor') == 'pantheon') {
      return dt('On Pantheon, you can technically write to syslog, but there is no mechanism for reading it. Disable syslog and enable dblog instead.');
    }
  }

  /**
   * Implements \SiteAudit\Check\Abstract\calculateScore().
   */
  public function calculateScore() {
    $this->registry['syslog_enabled'] = module_exists('syslog');
    if ($this->registry['syslog_enabled']) {
      if (drush_get_option('vendor') == 'pantheon') {
        return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL;
      }
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO;
  }
}
