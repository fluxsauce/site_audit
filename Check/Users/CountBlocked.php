<?php
/**
 * @file
 * Contains \SiteAudit\Check\Users\CountBlocked.
 */

class SiteAuditCheckUsersCountBlocked extends SiteAuditCheckAbstract {
  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Count Blocked');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Total number of blocked Drupal users.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getResultInfo().
   */
  public function getResultInfo() {
    if ($this->registry['count_users_blocked'] == 0) {
      return dt('There are no blocked users.');
    }
    if ($this->registry['count_users_blocked'] == 1) {
      return dt('There is one blocked user.');
    }
    return dt('There are @count_users_blocked blocked users.', array(
      '@count_users_blocked' => $this->registry['count_users_blocked'],
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
    $sql_query  = 'SELECT COUNT(uid) ';
    $sql_query .= 'FROM {users} ';
    $sql_query .= 'WHERE uid != 0 ';
    $sql_query .= 'AND status = 0 ';
    $this->registry['count_users_blocked'] = db_query($sql_query)->fetchField();
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO;
  }
}
