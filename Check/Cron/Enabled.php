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
  public function getResultInfo() {
    if (module_exists('elysia_cron')) {
      return dt('Drupal Cron is disabled, but Elysia Cron is being used instead.');
    }
    if (module_exists('ultimate_cron')) {
      return dt('Drupal Cron is disabled, but Ultimate Cron is being used instead.');
    }
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultPass().
   */
  public function getResultPass() {
    // Manual execution.
    if (!$this->registry['cron_safe_threshold']) {
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
    // Determine when cron last ran.
    $this->registry['cron_last'] = variable_get('cron_last');
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
      // Check for Elysia Cron.
      if (module_exists('elysia_cron')) {
        $this->abort = TRUE;
        return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO;
      }
      // Check for Ultimate Cron.
      if (module_exists('ultimate_cron')) {
        $this->abort = TRUE;
        return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO;
      }
      // Check to see if Cron has been run within the last day.
      if ((time() - $this->registry['cron_last']) < (24 * 60 * 60)) {
        return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS;
      }
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL;
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS;
  }
}
