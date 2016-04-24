<?php
/**
 * @file
 * Contains \SiteAudit\Check\Users\RolesPermissions.
 */

/**
 * Class SiteAuditCheckRolesRolesPermissions.
 */
class SiteAuditCheckRolesRolesPermissions extends SiteAuditCheckAbstract {

  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Roles and Permissions');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Percentage of permissions assigned to individual roles.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getResultInfo().
   */
  public function getResultInfo() {
    $roles = array();
    $total = array_sum($this->registry['roles']);
    foreach ($this->registry['roles'] as $name => $count_permissions) {
      $percentage = number_format(($count_permissions / $total) * 100, 0);
      $roles[] = "$name: $count_permissions ($percentage%)";
    }
    return implode(', ', $roles);
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultPass().
   */
  public function getResultPass() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getResultWarn().
   *
   * @TODO: do we check for admin perms in roles here?
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
    $sql_query  = 'SELECT name';
    $sql_query .= ', COUNT(uid) AS count_permissions ';
    $sql_query .= 'FROM {role} ';
    $sql_query .= 'LEFT JOIN {role_permission} ON {role}.rid = {role_permission}.rid ';
    $sql_query .= 'GROUP BY {role}.rid ';
    $sql_query .= 'ORDER BY name ASC ';
    $result = db_query($sql_query);
    foreach ($result as $row) {
      $this->registry['roles'][$row->name] = $row->count_permissions;
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO;
  }

}
