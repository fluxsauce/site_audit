<?php
/**
 * @file
 * Contains /site_audit/tests/CachePreprocessJsCssCase.
 */

namespace Unish;

/**
 * Class CachePreprocessJsCssCase.
 *
 * @group commands
 */
class CachePreprocessJsCssCase extends CommandUnishTestCase {

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
   * Set js.preprocess to false. Check should Fail.
   */
  public function testPreprocessJsFail() {
    $eval1 = "\$config = \\Drupal::configFactory()->getEditable('system.performance'); \$config->set('js.preprocess', FALSE); \$config->save();";
    $this->drush('php-eval', array($eval1), $this->options);
    $this->drush('audit-cache', array(), $this->options + array(
        'detail' => NULL,
        'json' => NULL,
      ));
    $output = json_decode($this->getOutput());
    $this->assertEquals(\SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL, $output->checks->SiteAuditCheckCachePreprocessJs->score);
  }

  /**
   * Set js.preprocess to true. Check should Pass.
   */
  public function testPreprocessJsPass() {
    $eval1 = "\$config = \\Drupal::configFactory()->getEditable('system.performance'); \$config->set('js.preprocess', TRUE); \$config->save();";
    $this->drush('php-eval', array($eval1), $this->options);
    $this->drush('audit-cache', array(), $this->options + array(
        'detail' => NULL,
        'json' => NULL,
      ));
    $output = json_decode($this->getOutput());
    $this->assertEquals(\SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS, $output->checks->SiteAuditCheckCachePreprocessJs->score);
  }

  /**
   * Set js.preprocess to false. Check should Fail.
   */
  public function testPreprocessCssFail() {
    $eval1 = "\$config = \\Drupal::configFactory()->getEditable('system.performance'); \$config->set('css.preprocess', FALSE); \$config->save();";
    $this->drush('php-eval', array($eval1), $this->options);
    $this->drush('audit-cache', array(), $this->options + array(
        'detail' => NULL,
        'json' => NULL,
      ));
    $output = json_decode($this->getOutput());
    $this->assertEquals(\SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL, $output->checks->SiteAuditCheckCachePreprocessCss->score);
  }

  /**
   * Set js.preprocess to true. Check should Pass.
   */
  public function testPreprocessCssPass() {
    $eval1 = "\$config = \\Drupal::configFactory()->getEditable('system.performance'); \$config->set('css.preprocess', TRUE); \$config->save();";
    $this->drush('php-eval', array($eval1), $this->options);
    $this->drush('audit-cache', array(), $this->options + array(
        'detail' => NULL,
        'json' => NULL,
      ));
    $output = json_decode($this->getOutput());
    $this->assertEquals(\SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS, $output->checks->SiteAuditCheckCachePreprocessCss->score);
  }

}
