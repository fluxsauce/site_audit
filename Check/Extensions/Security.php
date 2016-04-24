<?php
/**
 * @file
 * Contains \SiteAudit\Check\Extensions\Security.
 */

/**
 * Class SiteAuditCheckExtensionsSecurity.
 */
class SiteAuditCheckExtensionsSecurity extends SiteAuditCheckAbstract {

  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Security');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Determine what projects should be updated due to security concerns.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {
    $ret_val = 'The following project(s) have security releases available:';
    if (drush_get_option('html')) {
      $ret_val = '<p>' . $ret_val . '</p>';
      $ret_val .= '<table class="table table-condensed">';
      $ret_val .= '<thead><tr><th>' . dt('Name') . '</th><th>' . dt('Existing') . '</th><th>' . dt('Candidate') . '</th></thead>';
      $ret_val .= '<tbody>';
      foreach ($this->registry['projects_security'] as $short_info) {
        $ret_val .= '<tr>';
        $ret_val .= '<td>' . $short_info['label'] . '</td>';
        $ret_val .= '<td>' . $short_info['existing_version'] . '</td>';
        $ret_val .= '<td>' . $short_info['candidate_version'] . '</td>';
        $ret_val .= '</tr>';
      }
      $ret_val .= '</tbody>';
      $ret_val .= '</table>';
    }
    else {
      foreach ($this->registry['projects_security'] as $short_info) {
        $ret_val .= PHP_EOL;
        if (!drush_get_option('json')) {
          $ret_val .= str_repeat(' ', 6);
        }
        $ret_val .= "- {$short_info['label']}: {$short_info['existing_version']} to {$short_info['candidate_version']}";
      }
    }
    return $ret_val;
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultInfo().
   */
  public function getResultInfo() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getResultPass().
   */
  public function getResultPass() {
    return dt('No projects have security releases.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultWarn().
   */
  public function getResultWarn() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getAction().
   */
  public function getAction() {
    return dt('As a top priority, back up your site, review each project change, ensure compatibility, then update affected project(s).');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\calculateScore().
   */
  public function calculateScore() {
    if (!empty($this->registry['projects_security'])) {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL;
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS;
  }

}
