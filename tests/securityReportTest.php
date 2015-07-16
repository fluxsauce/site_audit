<?php
/**
 * @file
 * Contains /site_audit/tests/SecurityReportCase.
 */

namespace Unish;

/**
 * Class SecurityReportCase.
 *
 * @group commands
 */
class SecurityReportCase extends CommandUnishTestCase {

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
   * Check should pass on a default installation.
   */
  public function testMenuRouterPass() {
    $this->drush('audit-security', array(), $this->options + array(
        'detail' => NULL,
        'json' => NULL,
      ));
    $output = $this->getOutput();
    $output = json_decode($output);
    $this->assertEquals(\SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS, $output->checks->SiteAuditCheckSecurityMenuRouter->score);
  }
  /**
   * Enable a module with malicious menu router entry. Check should Fail.
   */
  public function testMenuRouterFail() {
    $dir = dirname(__DIR__) . '/tests/menu_router_test';
    \symlink($dir, $this->options['root'] . '/modules/menu_router_test');
    $this->drush('pm-enable', array('menu_router_test'), $this->options);
    $this->drush('audit-security', array(), $this->options + array(
        'detail' => NULL,
        'json' => NULL,
      ));
    $output = $this->getOutput();
    $output = json_decode($output);
    $this->assertEquals(\SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL, $output->checks->SiteAuditCheckSecurityMenuRouter->score);
  }

}
