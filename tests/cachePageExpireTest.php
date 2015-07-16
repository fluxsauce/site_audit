<?php
/**
 * @file
 * Contains /site_audit/tests/CachePageExpireCase.
 */

namespace Unish;

/**
 * Class CachePageExpireCase.
 *
 * @group commands
 */
class CachePageExpireCase extends CommandUnishTestCase {

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
   * Set cache.page.max_age to 0. Check should Fail.
   */
  public function testPageExpireOne() {
    $eval1 = "\$config = \\Drupal::configFactory()->getEditable('system.performance'); \$config->set('cache.page.max_age', 0); \$config->save();";
    $this->drush('php-eval', array($eval1), $this->options);
    $this->drush('audit-cache', array(), $this->options + array(
        'detail' => NULL,
        'json' => NULL,
      ));
    $output = json_decode($this->getOutput());
    $this->assertEquals(\SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL, $output->checks->SiteAuditCheckCachePageExpire->score);
  }

  /**
   * Set cache.page.max_age between 0 and 900. Check should Warn.
   */
  public function testPageExpireTwo() {
    $eval1 = "\$config = \\Drupal::configFactory()->getEditable('system.performance'); \$config->set('cache.page.max_age', rand(1,89)); \$config->save();";
    $this->drush('php-eval', array($eval1), $this->options);
    $this->drush('audit-cache', array(), $this->options + array(
        'detail' => NULL,
        'json' => NULL,
      ));
    $output = json_decode($this->getOutput());
    $this->assertEquals(\SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN, $output->checks->SiteAuditCheckCachePageExpire->score);
  }

  /**
   * Set cache.page.max_age greater than 900. Check should Pass.
   */
  public function testPageExpireThree() {
    $eval1 = "\$config = \\Drupal::configFactory()->getEditable('system.performance'); \$config->set('cache.page.max_age', 901); \$config->save();";
    $this->drush('php-eval', array($eval1), $this->options);
    $this->drush('audit-cache', array(), $this->options + array(
        'detail' => NULL,
        'json' => NULL,
      ));
    $output = json_decode($this->getOutput());
    $this->assertEquals(\SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS, $output->checks->SiteAuditCheckCachePageExpire->score);
  }

}
