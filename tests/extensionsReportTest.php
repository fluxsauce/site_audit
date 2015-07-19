<?php
/**
 * @file
 * Contains /site_audit/tests/ExtensionsReportCase.
 */

namespace Unish;

require_once 'Abstract.php';

/**
 * Class ExtensionsReportCase.
 *
 * @group commands
 */
class ExtensionsReportCase extends SiteAuditTestAbstract {

  /**
   * Sets up the environment for this test.
   */
  public function setUp() {
    $this->setUpSiteAuditTestEnvironment();
  }

  /**
   * Check should pass on a default installation.
   */
  public function testDevPass() {
    $this->drush('audit-extensions', array(), $this->options + array(
        'detail' => NULL,
        'json' => NULL,
      ));
    $output = json_decode($this->getOutput());
    $this->assertEquals(\SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS, $output->checks->SiteAuditCheckExtensionsDev->score);
  }

  /**
   * Enable a dev extension. Should Warn.
   */
  public function testDevWarn() {
    $this->drush('pm-download', array('ipsum'), $this->options);
    $this->drush('pm-enable', array('ipsum'), $this->options);
    $this->drush('audit-extensions', array(), $this->options + array(
        'detail' => NULL,
        'json' => NULL,
      ));
    $output = json_decode($this->getOutput());
    $this->assertEquals(\SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN, $output->checks->SiteAuditCheckExtensionsDev->score);

  }

  /**
   * Check should pass on a default installation.
   */
  public function testUnrecommendedPass() {
    $this->drush('audit-extensions', array(), $this->options + array(
        'detail' => NULL,
        'json' => NULL,
      ));
    $output = json_decode($this->getOutput());
    $this->assertEquals(\SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS, $output->checks->SiteAuditCheckExtensionsUnrecommended->score);
  }

  /**
   * Enable a unrecommended extension. Should Warn.
   */
  public function testUnrecommendedWarn() {
    $this->drush('pm-download', array('php'), $this->options);
    $this->drush('pm-enable', array('php'), $this->options);
    $this->drush('audit-extensions', array(), $this->options + array(
        'detail' => NULL,
        'json' => NULL,
      ));
    $output = json_decode($this->getOutput());
    $this->assertEquals(\SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL, $output->checks->SiteAuditCheckExtensionsUnrecommended->score);
  }

  /**
   * Download Two copies of an extension. Check should warn.
   */
  public function testDuplicateWarn1() {
    $this->drush('pm-download', array('php'), $this->options);
    $this->drush('pm-download', array('php'), $this->options + array('destination' => 'profiles'));
    $this->drush('pm-enable', array('php'), $this->options);
    $this->drush('audit-extensions', array(), $this->options + array(
        'detail' => NULL,
        'json' => NULL,
      ));
    $output = json_decode($this->getOutput());
    $this->assertEquals(\SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN, $output->checks->SiteAuditCheckExtensionsDuplicate->score);
  }

  /**
   * Multiple versions of the same module, one in profile, not enabled. Warn.
   */
  public function testDuplicateWarn2() {
    \mkdir($this->options['root'] . '/profiles/standard');
    \mkdir($this->options['root'] . '/profiles/standard/modules');
    $this->drush('pm-download', array('rules-8.x-3.0-unstable4'), $this->options);
    $this->drush('pm-download', array('rules-8.x-3.0-unstable3'), $this->options + array('destination' => 'profiles/standard/modules'));
    $this->drush('audit-extensions', array(), $this->options + array(
        'detail' => NULL,
        'json' => NULL,
      ));
    $output = json_decode($this->getOutput());
    $this->assertEquals(\SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN, $output->checks->SiteAuditCheckExtensionsDuplicate->score);
  }

  /**
   * Multiple versions of the same module, one in profile and enabled. Pass.
   */
  public function testDuplicatePass() {
    \mkdir($this->options['root'] . '/profiles/standard');
    \mkdir($this->options['root'] . '/profiles/standard/modules');
    $this->drush('pm-download', array('rules-8.x-3.0-unstable4'), $this->options);
    $this->drush('pm-download', array('rules-8.x-3.0-unstable3'), $this->options + array('destination' => 'profiles/standard/modules'));
    $this->drush('pm-enable', array('rules'), $this->options);
    $this->drush('audit-extensions', array(), $this->options + array(
        'detail' => NULL,
        'json' => NULL,
      ));
    $output = json_decode($this->getOutput());
    $this->assertEquals(\SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS, $output->checks->SiteAuditCheckExtensionsDuplicate->score);
  }

  /**
   * Multiple versions of the same module, higher one in profile and enabled.
   *
   * Check should Warn.
   */
  public function testDuplicateWarn3() {
    \mkdir($this->options['root'] . '/profiles/standard');
    \mkdir($this->options['root'] . '/profiles/standard/modules');
    $this->drush('pm-download', array('rules-8.x-3.0-unstable3'), $this->options);
    $this->drush('pm-download', array('rules-8.x-3.0-unstable4'), $this->options + array('destination' => 'profiles/standard/modules'));
    $this->drush('pm-enable', array('rules'), $this->options);
    $this->drush('audit-extensions', array(), $this->options + array(
        'detail' => NULL,
        'json' => NULL,
      ));
    $output = json_decode($this->getOutput());
    $this->assertEquals(\SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN, $output->checks->SiteAuditCheckExtensionsDuplicate->score);
  }

}
