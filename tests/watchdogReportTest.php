<?php
/**
 * @file
 * Contains /site_audit/tests/WatchdogReportCase.
 */

namespace Unish;

require_once 'Abstract.php';

/**
 * Class WatchdogReportCase.
 *
 * @group commands
 */
class WatchdogReportCase extends SiteAuditTestAbstract {

  /**
   * Sets up the environment for this test.
   */
  public function setUp() {
    $this->setUpSiteAuditTestEnvironment();
  }

  /**
   * Block the user with uid 1. Check should Fail.
   */
  public function test404Warn() {
    $this->drush('en', array('dblog'), $this->options);
    $eval1 = "\\Drupal::logger('page not found')->warning('drush tests');";
    for ($i = 0; $i < 100; $i++) {
      $this->drush('php-eval', array($eval1), $this->options);
    }
    $this->drush('audit-watchdog', array(), $this->options + array(
      'detail' => NULL,
      'json' => NULL,
    ));
    $output = $this->getOutput();
    $output = json_decode($output);
    $this->assertEquals(\SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN, $output->checks->SiteAuditCheckWatchdog404->score);
  }

  /**
   * Create more than 10% php errors. Should Warn.
   */
  public function testPhpWarn() {
    $this->drush('en', array('dblog'), $this->options);
    $eval1 = "\\Drupal::logger('php')->warning('drush tests');";
    for ($i = 0; $i < 100; $i++) {
      $this->drush('php-eval', array($eval1), $this->options);
    }
    $this->drush('audit-watchdog', array(), $this->options + array(
        'detail' => NULL,
        'json' => NULL,
      ));
    $output = $this->getOutput();
    $output = json_decode($output);
    $this->assertEquals(\SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN, $output->checks->SiteAuditCheckWatchdogPhp->score);
  }

}
