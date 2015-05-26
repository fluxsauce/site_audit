<?php
/**
 * @file
 * Contains \SiteAudit\Check\Users\RolesList.
 */

/**
 * Class SiteAuditCheckUsersRolesList.
 */
class SiteAuditCheckUsersRolesList extends SiteAuditCheckAbstract {
  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('List Roles');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Show all available roles and user counts.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getResultInfo().
   */
  public function getResultInfo() {
    $counts = array();
    foreach ($this->registry['roles'] as $name => $count_users) {
      $counts[] = "$name: $count_users";
    }
    return implode(', ', $counts);
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
    $roles = array_keys(\Drupal::entityManager()->getListBuilder('user_role')->load());
    foreach ($roles as $role) {
      if ($role != "anonymous" && $role != "authenticated") {
        $sql_query = 'SELECT COUNT(entity_id) AS count_users ';
        $sql_query .= 'FROM {user__roles} ';
        $sql_query .= 'WHERE roles_target_id = :role';
        $result = db_query($sql_query, array('role' => $role));
        $this->registry['roles'][$role] = $result->fetchAssoc()['count_users'];
      }
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO;
  }

}
