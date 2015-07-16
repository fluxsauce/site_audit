<?php
/**
 * @file
 * Contains /site_audit/tests/BestPracticesFast404Case.
 */

namespace Unish;

/**
 * Class BestPracticesFast404Case.
 *
 * @group commands
 */
class BestPracticesFast404Case extends CommandUnishTestCase {

  /**
   * Sets up the environment for this test.
   */
  public function setUp() {
    $site = $this->setUpDrupal(1, TRUE, UNISH_DRUPAL_MAJOR_VERSION);
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
   * If fast_404 is enabled and fast_404 paths are empty, check should warn.
   */
  public function testFast404One() {
    // Enable fast_404 and make fast_404 paths empty.
    $eval1 = "\$config = \\Drupal::configFactory()->getEditable('system.performance'); \$config->set('fast_404.enabled', TRUE); \$config->save();";
    $eval2 = "\$config = \\Drupal::configFactory()->getEditable('system.performance'); \$config->set('fast_404.paths', ''); \$config->save();";
    $this->drush('php-eval', array($eval1), $this->options);
    $this->drush('php-eval', array($eval2), $this->options);

    // Execute the best-practices command and get output.
    $this->drush('audit-best-practices', array(), $this->options + array('detail' => NULL, 'json' => NULL));
    $output = json_decode($this->getOutput());
    $this->assertEquals(\SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN, $output->checks->SiteAuditCheckBestPracticesFast404->score);
  }

  /**
   * Fast_404 enabled and fast_404 paths not empty in fast_404, check passes.
   */
  public function testFast404Two() {
    $eval1 = "\$config = \\Drupal::configFactory()->getEditable('system.performance'); \$config->set('fast_404.enabled', TRUE); \$config->save();";
    $eval2 = "\$config = \\Drupal::configFactory()->getEditable('system.performance'); \$config->set('fast_404.paths', '/\\.(?:txt|png)$/i'); \$config->save();";
    $this->drush('php-eval', array($eval1), $this->options);
    $this->drush('php-eval', array($eval2), $this->options);

    $this->drush('audit-best-practices', array(), $this->options + array('detail' => NULL, 'json' => NULL));
    $output = json_decode($this->getOutput());
    $this->assertEquals(\SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS, $output->checks->SiteAuditCheckBestPracticesFast404->score);

  }

  /**
   * If fast_404 is disabled, check should warn.
   */
  public function testFast404Three() {
    $eval1 = "\$config = \\Drupal::configFactory()->getEditable('system.performance'); \$config->set('fast_404.enabled', FALSE); \$config->save();";
    $this->drush('php-eval', array($eval1), $this->options);
    $this->drush('audit-best-practices', array(), $this->options + array('detail' => NULL, 'json' => NULL));
    $output = json_decode($this->getOutput());
    $this->assertEquals(\SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN, $output->checks->SiteAuditCheckBestPracticesFast404->score);
  }

}
