<?php
/**
 * @file
 * Contains /site_audit/tests/CronReportCase.
 */

namespace Unish;

require_once 'Abstract.php';

/**
 * Class CronReportCase.
 *
 * @group commands
 */
class CronReportCase extends SiteAuditTestAbstract {

  /**
   * Sets up the environment for this test.
   */
  public function setUp() {
    $this->setUpSiteAuditTestEnvironment();
  }

  /**
   * Run the cron and set cron frequency to < 24 hours. Check should Pass.
   */
  public function testEnabledPassOne() {
    $eval1 = <<<EOT
\$config = \\Drupal::configFactory()->getEditable('system.cron');
\$config->set('threshold.autorun', 60*60);
\$config->save();
EOT;
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
    $eval1 = <<<EOT
\$config = \\Drupal::configFactory()->getEditable('system.cron');
\$config->set('threshold.autorun', 25*60*60);
\$config->save();
EOT;
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
    $eval1 = <<<EOT
\$config = \\Drupal::configFactory()->getEditable('system.cron');
\$config->set('threshold.autorun', 0);
\$config->save();
\\Drupal::state()->set('system.cron_last', NULL)
EOT;
    $this->drush('php-eval', array($eval1), $this->options);
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
    $eval1 = <<<EOT
\$config = \\Drupal::configFactory()->getEditable('system.cron');
\$config->set('threshold.autorun', 0);
\$config->save();
EOT;
    $this->drush('php-eval', array($eval1), $this->options);
    $this->drush('cron', array(), $this->options);
    $this->drush('audit-cron', array(), $this->options + array(
        'detail' => NULL,
        'json' => NULL,
      ));
    $output = json_decode($this->getOutput());
    $this->assertEquals(\SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS, $output->checks->SiteAuditCheckCronEnabled->score);
  }

}
