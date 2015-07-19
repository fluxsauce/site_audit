<?php
/**
 * @file
 * Contains /site_audit/tests/UsersReportCase.
 */

namespace Unish;

require_once 'Abstract.php';

/**
 * Class UsersReportCase.
 *
 * @group commands
 */
class UsersReportCase extends SiteAuditTestAbstract {

  /**
   * Sets up the environment for this test.
   */
  public function setUp() {
    $this->setUpSiteAuditTestEnvironment();
  }

  /**
   * Block the user with uid 1. Check should Fail.
   */
  public function testBlockedNumberOneFail() {
    $this->drush('user-block', array(1), $this->options);
    $this->drush('audit-users', array(), $this->options + array(
        'detail' => NULL,
        'json' => NULL,
      ));
    $output = json_decode($this->getOutput());
    $this->assertEquals(\SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL, $output->checks->SiteAuditCheckUsersBlockedNumberOne->score);
  }

  /**
   * UnBlock the user with uid 1. Check should Pass.
   */
  public function testBlockedNumberOnePass() {
    $this->drush('user-unblock', array(1), $this->options);
    $this->drush('audit-users', array(), $this->options + array(
        'detail' => NULL,
        'json' => NULL,
      ));
    $output = json_decode($this->getOutput());
    $this->assertEquals(\SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS, $output->checks->SiteAuditCheckUsersBlockedNumberOne->score);
  }

  /**
   * Delete user with uid 1. Check should Fail.
   */
  public function testWhoIsNumberOneFail() {
    $this->drush('user-cancel', array(1), $this->options);
    // Add a new user so that user count is not zero which will cause CountAll
    // test to abort this report.
    $this->drush('user-create', array('site_audit'), $this->options + array('mail' => 'person@example.com', 'password' => 'site_audit'));
    $this->drush('audit-users', array(), $this->options + array(
        'detail' => NULL,
        'json' => NULL,
      ));
    $output = json_decode($this->getOutput());
    $this->assertEquals(\SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL, $output->checks->SiteAuditCheckUsersWhoIsNumberOne->score);
  }

}
