<?php
/**
 * @file
 * Contains /site_audit/tests/BestPracticesReportCase.
 */

namespace Unish;

require_once 'Abstract.php';

/**
 * Class BestPracticesReportCase.
 *
 * @group commands
 */
class BestPracticesReportCase extends SiteAuditTestAbstract {

  /**
   * Sets up the environment for this test.
   */
  public function setUp() {
    $this->setUpSiteAuditTestEnvironment();
  }
  /**
   * If fast_404 is enabled and fast_404 paths are empty, check should warn.
   */
  public function testFast404One() {
    // Enable fast_404 and make fast_404 paths empty.
    $eval1 = <<<EOT
\$config = \\Drupal::configFactory()->getEditable('system.performance');
\$config->set('fast_404.enabled', TRUE);
\$config->set('fast_404.paths', '');
\$config->save();
EOT;
    $this->drush('php-eval', array($eval1), $this->options);

    // Execute the best-practices command and get output.
    $this->drush('audit-best-practices', array(), $this->options + array('detail' => NULL, 'json' => NULL));
    $output = json_decode($this->getOutput());
    $this->assertEquals(\SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN, $output->checks->SiteAuditCheckBestPracticesFast404->score);
  }

  /**
   * Fast_404 enabled and fast_404 paths not empty in fast_404, check passes.
   */
  public function testFast404Two() {
    $eval1 = <<<EOT
\$config = \\Drupal::configFactory()->getEditable('system.performance');
\$config->set('fast_404.enabled', TRUE);
\$config->set('fast_404.paths', '/\\.(?:txt|png)$/i');
\$config->save();;
EOT;
    $this->drush('php-eval', array($eval1), $this->options);

    $this->drush('audit-best-practices', array(), $this->options + array('detail' => NULL, 'json' => NULL));
    $output = json_decode($this->getOutput());
    $this->assertEquals(\SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS, $output->checks->SiteAuditCheckBestPracticesFast404->score);

  }

  /**
   * If fast_404 is disabled, check should warn.
   */
  public function testFast404Three() {
    $eval1 = <<<EOT
\$config = \\Drupal::configFactory()->getEditable('system.performance');
\$config->set('fast_404.enabled', FALSE);
\$config->save();
EOT;
    $this->drush('php-eval', array($eval1), $this->options);
    $this->drush('audit-best-practices', array(), $this->options + array('detail' => NULL, 'json' => NULL));
    $output = json_decode($this->getOutput());
    $this->assertEquals(\SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN, $output->checks->SiteAuditCheckBestPracticesFast404->score);
  }

}
