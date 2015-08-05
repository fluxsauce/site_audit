<?php
/**
 * @file
 * Contains /site_audit/tests/SiteAuditTestAbstract.
 */

namespace Unish;

/**
 * Class SiteAuditTestAbstract.
 */
abstract class SiteAuditTestAbstract extends CommandUnishTestCase {

  /**
   * Sets up environment for running site_audit tests.
   */
  public function setUpSiteAuditTestEnvironment() {
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

}
