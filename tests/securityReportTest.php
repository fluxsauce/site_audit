<?php
/**
 * @file
 * Contains /site_audit/tests/SecurityReportCase.
 */

namespace Unish;

require_once 'Abstract.php';

/**
 * Class SecurityReportCase.
 *
 * @group commands
 */
class SecurityReportCase extends SiteAuditTestAbstract {

  /**
   * Sets up the environment for this test.
   */
  public function setUp() {
    $this->setUpSiteAuditTestEnvironment();
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
