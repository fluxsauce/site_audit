<?php
/**
 * @file
 * Contains \SiteAudit\Check\Extensions\Update.
 */

/**
 * Class SiteAuditCheckExtensionsUpdate.
 */
class SiteAuditCheckExtensionsUpdate extends SiteAuditCheckAbstract {

  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Updates');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Determine what projects can be updated.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getResultInfo().
   */
  public function getResultInfo() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getResultPass().
   */
  public function getResultPass() {
    return dt('No projects need updating.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultWarn().
   */
  public function getResultWarn() {
    $ret_val = 'The following project(s) have updates available:';
    if (drush_get_option('html')) {
      $ret_val = '<p>' . $ret_val . '</p>';
      $ret_val .= '<table class="table table-condensed">';
      $ret_val .= '<thead><tr><th>' . dt('Name') . '</th><th>' . dt('Existing') . '</th><th>' . dt('Candidate') . '</th><th>' . dt('Status') . '</th></thead>';
      $ret_val .= '<tbody>';
      foreach ($this->registry['projects_update'] as $short_info) {
        $ret_val .= '<tr>';
        $ret_val .= '<td>' . $short_info['label'] . '</td>';
        $ret_val .= '<td>' . $short_info['existing_version'] . '</td>';
        $ret_val .= '<td>' . $short_info['candidate_version'] . '</td>';
        $ret_val .= '<td>' . $short_info['status_msg'] . '</td>';
        $ret_val .= '</tr>';
      }
      $ret_val .= '</tbody>';
      $ret_val .= '</table>';
    }
    else {
      foreach ($this->registry['projects_update'] as $short_info) {
        $ret_val .= PHP_EOL;
        if (!drush_get_option('json')) {
          $ret_val .= str_repeat(' ', 6);
        }
        $ret_val .= "- {$short_info['label']}: {$short_info['existing_version']} to {$short_info['candidate_version']} - {$short_info['status_msg']}";
      }
    }
    return $ret_val;
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getAction().
   */
  public function getAction() {
    return dt('Back up your site, review each project change, ensure compatibility, then update affected project(s).');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\calculateScore().
   */
  public function calculateScore() {
    $this->registry['projects_update'] = $this->registry['projects_security'] = array();

    $updatestatus_options = array(
      'check-disabled' => TRUE,
      'lock' => FALSE,
      'lock-message' => FALSE,
      'security-only' => FALSE,
      'unlock' => FALSE,
    );
    $backend_options = array(
      'integrate' => FALSE,
    );

    $values = drush_invoke_process('@self', 'pm-updatestatus', func_get_args(), $updatestatus_options, $backend_options);
    $update_info = $values['object'];

    // Only show updatable projects.
    foreach ($update_info as $name => $project) {
      if ((!isset($project['updateable']) || !$project['updateable'])) {
        unset($update_info[$name]);
      }
    }

    // Build only useful data.
    foreach ($update_info as $project_name => $project_data) {
      $short_info = array(
        'existing_version' => $project_data['existing_version'],
        'candidate_version' => $project_data['candidate_version'],
        'status_msg' => $project_data['status_msg'],
        'label' => $project_data['label'],
      );
      if (stripos($short_info['status_msg'], 'security') !== FALSE) {
        $this->registry['projects_security'][$project_name] = $short_info;
      }
      else {
        $this->registry['projects_update'][$project_name] = $short_info;
      }
    }

    if (!empty($this->registry['projects_update'])) {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN;
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS;
  }

}
