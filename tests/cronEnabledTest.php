<?php
/**
 * @file
 * Contains /site_audit/tests/CronEnabledCase.
 */

namespace Unish;

/**
 * Class CronEnabledCase.
 *
 * @group commands
 */
class CronEnabledCase extends CommandUnishTestCase {

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
   * Run the cron and set cron frequency to < 24 hours. Check should Pass.
   */
  public function testEnabledPassOne() {
    $eval1 = "\$config = \\Drupal::configFactory()->getEditable('system.cron'); \$config->set('threshold.autorun', 60*60); \$config->save();";
    $this->drush('php-eval', array($eval1), $this->options);
    $this->drush('cron', array(), $this->options);
    $this->drush('audit-cron', array(), $this->options + array(
        'detail' => NULL,
        'json' => NULL,
      ));
    $output = json_decode($this->getOutput());
    $this->assertEquals(\SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS, $output->checks->SiteAuditCheckCronEnabled->score);
  }

  /**
   * Run the cron and set cron frequency to > 24 hours. Check should Warn.
   */
  public function testEnabledWarn() {
    $eval1 = "\$config = \\Drupal::configFactory()->getEditable('system.cron'); \$config->set('threshold.autorun', 25*60*60); \$config->save();";
    $this->drush('php-eval', array($eval1), $this->options);
    $this->drush('cron', array(), $this->options);
    $this->drush('audit-cron', array(), $this->options + array(
        'detail' => NULL,
        'json' => NULL,
      ));
    $output = json_decode($this->getOutput());
    $this->assertEquals(\SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN, $output->checks->SiteAuditCheckCronEnabled->score);
  }

  /**
   * Set cron frequency to 0 and don't run cron. Check should Fail.
   */
  public function testEnabledFail() {
    $eval1 = "\$config = \\Drupal::configFactory()->getEditable('system.cron'); \$config->set('threshold.autorun', 0); \$config->save();";
    $eval2 = "\\Drupal::state()->set('system.cron_last', NULL)";
    $this->drush('php-eval', array($eval1), $this->options);
    $this->drush('php-eval', array($eval2), $this->options);
    $this->drush('audit-cron', array(), $this->options + array(
        'detail' => NULL,
        'json' => NULL,
      ));
    $output = json_decode($this->getOutput());
    $this->assertEquals(\SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL, $output->checks->SiteAuditCheckCronEnabled->score);
  }

  /**
   * Set cron frequency to 0 and run cron. Check should Pass.
   */
  public function testEnabledPassTwo() {
    $eval1 = "\$config = \\Drupal::configFactory()->getEditable('system.cron'); \$config->set('threshold.autorun', 0); \$config->save();";
    $this->drush('php-eval', array($eval1), $this->options);
    $this->drush('cron', array(), $this->options);
    $this->drush('audit-cron', array(), $this->options + array(
        'detail' => NULL,
        'json' => NULL,
      ));
    $output = json_decode($this->getOutput());
    $this->assertEquals(\SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN, $output->checks->SiteAuditCheckCronEnabled->score);
  }

}
