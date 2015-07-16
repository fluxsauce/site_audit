<?php
/**
 * @file
 * Contains /site_audit/tests/ExtensionsReportCase.
 */
namespace Unish;
/**
 * Class ExtensionsReportCase.
 *
 * @group commands
 */
class ExtensionsReportCase extends CommandUnishTestCase {
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
  public function testDevPass() {
    $this->drush('audit_extensions', array(), $this->options + array(
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
    $this->drush('pm-download', array('drupal_ipsum'), $this->options);
    $this->drush('pm-enable', array('drupal_ipsum'), $this->options);
    $this->drush('audit_extensions', array(), $this->options + array(
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
    $this->drush('audit_extensions', array(), $this->options + array(
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
    $this->drush('pm-download', array('misery'), $this->options);
    $this->drush('pm-enable', array('misery'), $this->options);
    $this->drush('audit_extensions', array(), $this->options + array(
        'detail' => NULL,
        'json' => NULL,
      ));
    $output = json_decode($this->getOutput());
    $this->assertEquals(\SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL, $output->checks->SiteAuditCheckExtensionsUnrecommended->score);
  }

  /**
   * Download Two copies of an extension. Check should warn.
   */
  public function testDuplicateWarn() {
    $this->drush('pm-download', array('misery'), $this->options);
    $this->drush('pm-download', array('misery'), $this->options + array('destination' => 'sites/all/profiles'));
    $this->drush('pm-enable', array('misery'), $this->options);
    $this->drush('audit_extensions', array(), $this->options + array(
        'detail' => NULL,
        'json' => NULL,
      ));
    $output = json_decode($this->getOutput());
    $this->assertEquals(\SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN, $output->checks->SiteAuditCheckExtensionsDuplicate->score);
  }

  /**
   * Enable an extension and then remove it. Check should Fail.
   */
  public function testMissingFail() {
    $this->drush('pm-download', array('misery'), $this->options);
    $this->drush('pm-enable', array('misery'), $this->options);
    $this->rrmdir($this->options['root'] . '/sites/all/modules/misery');
    $this->drush('audit_extensions', array(), $this->options + array(
        'detail' => NULL,
        'json' => NULL,
      ));
    $output = json_decode($this->getOutput());
    $this->assertEquals(\SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL, $output->checks->SiteAuditCheckExtensionsMissing->score);

  }

  /**
   * Remove a directory with all the files inside it.
   *
   * @param string $dir
   *   The directory to be removed.
   */
  public function rrmdir($dir) {
    if (is_dir($dir)) {
      $objects = scandir($dir);
      foreach ($objects as $object) {
        if ($object != "." && $object != "..") {
          if (filetype($dir . "/" . $object) == "dir") {
            $this->rrmdir($dir . "/" . $object);
          }
          else {
            unlink($dir . "/" . $object);
          }
        }
      }
      reset($objects);
      rmdir($dir);
    }
  }

}
