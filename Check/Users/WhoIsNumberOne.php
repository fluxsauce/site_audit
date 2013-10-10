<?php
/**
 * @file
 * Contains \SiteAudit\Check\Users\WhoIsNumberOne.
 */

class SiteAuditCheckUsersWhoIsNumberOne extends SiteAuditCheckAbstract {
  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Identify UID #1');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Show username and email of UID #1.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {
    return dt('UID #1 does not exist! This is a serious problem.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultInfo().
   */
  public function getResultInfo() {
    return dt('UID #1: @name, email: @mail', array(
      '@name' => $this->registry['uid_1']->name,
      '@mail' => $this->registry['uid_1']->mail,
    ));
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultPass().
   */
  public function getResultPass() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getResultWarn().
   */
  public function getResultWarn() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getAction().
   */
  public function getAction() {}

  /**
   * Implements \SiteAudit\Check\Abstract\calculateScore().
   */
  public function calculateScore() {
    $uid_1 = user_load(1);
    if (!$uid_1) {
      $this->abort = TRUE;
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL;
    }
    $this->registry['uid_1'] = $uid_1;
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO;
  }
}
