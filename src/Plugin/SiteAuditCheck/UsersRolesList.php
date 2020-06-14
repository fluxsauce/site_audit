<?php

namespace Drupal\site_audit\Plugin\SiteAuditCheck;

use Drupal\site_audit\Plugin\SiteAuditCheckBase;

/**
 * Provides the UsersRolesList Check.
 *
 * @SiteAuditCheck(
 *  id = "users_roles_list",
 *  name = @Translation("List Roles"),
 *  description = @Translation("Show all available roles and user counts."),
 *  report = "users"
 * )
 */
class UsersRolesList extends SiteAuditCheckBase {

  /**
   * {@inheritdoc}.
   */
  public function getResultFail() {}

  /**
   * {@inheritdoc}.
   */
  public function getResultInfo() {
    $counts = [];
    foreach ($this->registry->roles as $name => $count_users) {
      $counts[] = "$name: $count_users";
    }
    return implode('<br/>', $counts);
  }

  /**
   * {@inheritdoc}.
   */
  public function getResultPass() {}

  /**
   * {@inheritdoc}.
   */
  public function getResultWarn() {}

  /**
   * {@inheritdoc}.
   */
  public function getAction() {}

  /**
   * {@inheritdoc}.
   */
  public function calculateScore() {
    $query = \Drupal::database()->select('user__roles');
    $query->addExpression('COUNT(entity_id)', 'count');
    $query->addfield('user__roles', 'roles_target_id', 'name');
    $query->groupBy('name');
    $query->orderBy('name', 'ASC');
    $results = $query->execute();
    while ($row = $results->fetchObject()) {
      $this->registry->roles[$row->name] = $row->count;
    }
    return SiteAuditCheckBase::AUDIT_CHECK_SCORE_INFO;
  }

}
