<?php

namespace Drupal\site_audit\Plugin\SiteAuditCheck;

use Drupal\site_audit\Plugin\SiteAuditCheckBase;

/**
 * Provides the WatchdogSyslog Check.
 *
 * @SiteAuditCheck(
 *  id = "watchdog_syslog",
 *  name = @Translation("syslog status"),
 *  description = @Translation("Check to see if syslog logging is enabled"),
 *  report = "watchdog",
 *  weight = -10,
 * )
 */
class WatchdogSyslog extends SiteAuditCheckBase {

  /**
   * {@inheritdoc}.
   */
  public function getResultFail() {
    return $this->t('Syslog logging is enabled!');
  }

  /**
   * {@inheritdoc}.
   */
  public function getResultInfo() {
    if ($this->registry->syslog_enabled) {
      return $this->t('Syslog logging is enabled.');
    }
    return $this->t('Syslog logging is not enabled.');
  }

  /**
   * {@inheritdoc}.
   */
  public function getResultPass() {
    return $this->getResultInfo();
  }

  /**
   * {@inheritdoc}.
   */
  public function getResultWarn() {}

  /**
   * {@inheritdoc}.
   */
  public function getAction() {
    /* TODO configure settings in the web interface and from the DRUSH command line
    if ($this->getScore() == SiteAuditCheckBase::AUDIT_CHECK_SCORE_FAIL && $this->options['vendor'] == 'pantheon') {
    return $this->t('On Pantheon, you can technically write to syslog, but there is no mechanism for reading it. Disable syslog and enable dblog instead.');
    }
     */
  }

  /**
   * {@inheritdoc}.
   */
  public function calculateScore() {
    $this->registry->syslog_enabled = \Drupal::moduleHandler()->moduleExists('syslog');
    if ($this->registry->syslog_enabled) {
      /**
       * TODO sonfigure settings in the web interface and from the DRUSH command line
       * if ($this->options['vendor'] == 'pantheon') {
       * return SiteAuditCheckBase::AUDIT_CHECK_SCORE_FAIL;
       * }
      */
    }
    return SiteAuditCheckBase::AUDIT_CHECK_SCORE_INFO;
  }

}
