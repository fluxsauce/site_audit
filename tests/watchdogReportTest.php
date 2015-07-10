<?php
/**
 * @file
 * Contains /site_audit/tests/WatchdogReportCase.
 */

namespace Unish;

/**
 * Class WatchdogReportCase.
 *
 * @group commands
 */
class WatchdogReportCase extends CommandUnishTestCase {

  /**
   * Sets up the environment for this test.
   */
  public function setUp() {
    $site = $this->setUpDrupal(1, TRUE, UNISH_DRUPAL_MAJOR_VERSION, 'standard');
    $root = $this->webroot();
    $this->options = array(
      'yes' => NULL,
      'root' => $root,
      'uri' => key($site),
    );
    // Symlink site_audit inside the site being tested, so that it is available
    // as a drush command.
    $target = dirname(__DIR__);
    \mkdir($root . '/drush');
    \symlink($target, $this->webroot() . '/drush/site_audit');
    $this->drush('cache-clear', array('drush'), $this->options);
    require_once $target . '/Check/Abstract.php';
  }

  /**
   * Block the user with uid 1. Check should Fail.
   */
  public function test404Warn() {
    $eval1 = "\\Drupal::logger('page not found')->warning('drush tests');";
    for ($i = 0; $i < 100; $i++) {
      $this->drush('php-eval', array($eval1), $this->options);
    }
    $this->drush('en', array('dblog'), $this->options);
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
    $eval1 = "\\Drupal::logger('php')->warning('drush tests');";
    for ($i = 0; $i < 100; $i++) {
      $this->drush('php-eval', array($eval1), $this->options);
    }
    $this->drush('en', array('dblog'), $this->options);
    $this->drush('audit-watchdog', array(), $this->options + array(
        'detail' => NULL,
        'json' => NULL,
      ));
    $output = $this->getOutput();
    $output = json_decode($output);
    $this->assertEquals(\SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN, $output->checks->SiteAuditCheckWatchdogPhp->score);
  }

}
