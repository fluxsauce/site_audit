<?php
/**
 * @file
 * Contains \SiteAudit\Check\Codebase\GitContributor.
 */

/**
 * Class SiteAuditCheckCodebaseGitContributor.
 */
class SiteAuditCheckCodebaseGitContributor extends SiteAuditCheckAbstract {
  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Git Contributors');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Determine lines of code added and removed by each contributor.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getResultInfo().
   */
  public function getResultInfo() {
    if (isset($this->registry['not_git'])) {
      return dt('Site is not under git source control');
    }
    $ret_val = '';
    if (drush_get_option('html') == TRUE) {
      $ret_val .= '<table class="table table-condensed">';
      $ret_val .= '<thead><tr><th>' . dt('Author') . '</th><th>' . dt('Lines Inserted') . '</th><th>' . dt('Lines Deleted') . '</th><th>' . dt('Percentage Contribution') . '</th></tr></thead>';
      foreach ($this->registry['git_contribution_percentage'] as $user => $percentage) {
        $added = $this->registry['git_contribution'][$user]['inserted'];
        $deleted = $this->registry['git_contribution'][$user]['deleted'];
        $ret_val .= "<tr><td>$user</td><td>$added</td><td>$deleted</td><td>$percentage</td></tr>";
      }
      $ret_val .= '</table>';
    }
    else {
      $ret_val  = dt('Author: Inserted, Deleted, Percentage') . PHP_EOL;
      if (!drush_get_option('json')) {
        $ret_val .= str_repeat(' ', 4);
      }
      $ret_val .= '-----------------------------';
      foreach ($this->registry['git_contribution_percentage'] as $user => $percentage) {
        $ret_val .= PHP_EOL;
        if (!drush_get_option('json')) {
          $ret_val .= str_repeat(' ', 4);
        }
        $added = $this->registry['git_contribution'][$user]['inserted'];
        $deleted = $this->registry['git_contribution'][$user]['deleted'];
        $ret_val .= "$user: $added, $deleted, $percentage";
      }
    }
    return $ret_val;

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
    // Check if site is a git repository.
    $is_git = exec('git rev-parse --is-inside-work-tree 2> /dev/null');
    if ($is_git !== 'true') {
      $this->registry['not_git'] = TRUE;
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO;
    }
    // Get all users.
    $users = array();
    exec("git log --format='%aN' | sort -u", $users);

    // Get the lines of code for each user.
    $total = 0;
    foreach ($users as $user) {
      $command = "git log --no-merges --shortstat --author '$user' 2> /dev/null | grep 'files\\? changed' 2> /dev/null | awk '{inserted+=$4; deleted+=$6} END {print inserted, deleted}' 2> /dev/null";
      $output = explode(' ', exec($command));
      $this->registry['git_contribution'][$user] = array(
        'inserted' => $output[0],
        'deleted' => $output[1],
      );
      $total += intval($output[0]) + intval($output[1]);
    }
    foreach ($users as $user) {
      $inserted = intval($this->registry['git_contribution'][$user]['inserted']);
      $deleted = intval($this->registry['git_contribution'][$user]['deleted']);
      $percentage = number_format((($inserted + $deleted) / $total) * 100, 2);
      $this->registry['git_contribution_percentage'][$user] = $percentage;
    }

    arsort($this->registry['git_contribution_percentage']);
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO;
  }

}
