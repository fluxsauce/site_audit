<?php
/**
 * @file
 * Contains \SiteAudit\Check\Users\CountAll.
 */

class SiteAuditCheckUsersCountAll extends SiteAuditCheckAbstract {
  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Count All');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Total number of Drupal users.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {
    return dt('There are no users!');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultInfo().
   */
  public function getResultInfo() {
    if ($this->registry['count_users_all'] == 1) {
      return dt('There is one user.');
    }
    return dt('There are @count_users_all users.', array(
      '@count_users_all' => $this->registry['count_users_all'],
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
    $this->registry['count_users_all'] = db_query($sql_query)->fetchField();
    if (!$this->registry['count_users_all']) {
      $this->abort = TRUE;
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL;
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO;
  }
}
