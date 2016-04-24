<?php
/**
 * @file
 * Contains \SiteAudit\Check\Cron\Enabled.
 */

/**
 * Class SiteAuditCheckCronEnabled.
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
  public function getResultInfo() {
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultPass().
   */
  public function getResultPass() {
    // Manual execution.
    if ($this->registry['cron_safe_threshold'] === 0) {
      return dt('Drupal Cron frequency is set to never, but has been executed within the past 24 hours (either manually or using drush cron).');
    }
    // Default.
    return dt('Cron is set to run every @minutes minutes.', array(
      '@minutes' => round($this->registry['cron_safe_threshold'] / 60),
    ));
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultWarn().
   */
  public function getResultWarn() {
    if ($this->registry['cron_safe_threshold'] > (24 * 60 * 60)) {
      return dt('Drupal Cron frequency is set to mare than 24 hours.');
    }
    else {
      return dt("Drupal Cron has not run in the past day even though it's frequency has been set to less than 24 hours.");
    }
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getAction().
   */
  public function getAction() {
    if ($this->score == SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL) {
      return dt('Please visit /admin/config/system/cron and set the cron frequency to something other than Never but less than 24 hours.');
    }
    elseif ($this->score == SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN) {
      if ($this->registry['cron_safe_threshold'] > (24 * 60 * 60)) {
        return dt('Please visit /admin/config/system/cron and set the cron frequency to something less than 24 hours.');
      }
    }
  }

  /**
   * Implements \SiteAudit\Check\Abstract\calculateScore().
   */
  public function calculateScore() {
    // Determine when cron last ran.
    $this->registry['cron_last'] = \Drupal::state()->get('system.cron_last');
    $this->registry['cron_safe_threshold'] = \Drupal::config('system.cron')->get('threshold.autorun');

    // Cron hasn't run in the past day.
    if ((time() - $this->registry['cron_last']) > (24 * 60 * 60)) {
      if ($this->registry['cron_safe_threshold'] === 0) {
        return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL;
      }
      else {
        return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN;
      }
    }
    elseif ($this->registry['cron_safe_threshold'] > (24 * 60 * 60)) {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN;
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS;
  }

}
