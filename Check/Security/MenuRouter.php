<?php
/**
 * @file
 * Contains \SiteAudit\Check\Security\MenuRouter.
 */

class SiteAuditCheckSecurityMenuRouter extends SiteAuditCheckAbstract {
  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Menu Router');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Check for malicious entries in the menu router.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {
    $ret_val = dt('The following malicious paths have been discovered: @list', array(
      '@list' => implode(', ', array_keys($this->registry['menu_router'])),
    ));

    if (drush_get_option('detail')) {
      if (drush_get_option('html')) {
        $ret_val .= '<br/>';
        $ret_val .= '<table class="table table-condensed">';
        $ret_val .= '<thead><tr><th>Path</th><th>Reason</th></thead>';
        $ret_val .= '<tbody>';
        foreach ($this->registry['menu_router'] as $path => $reason) {
          $ret_val .= '<tr><td>' . $path . '</td><td>' . $reason . '</td></tr>';
        }
        $ret_val .= '</tbody>';
        $ret_val .= '</table>';
      }
      else {
        foreach ($this->registry['menu_router'] as $path => $reason) {
          $ret_val .= PHP_EOL;
          if (!drush_get_option('json')) {
            $ret_val .= str_repeat(' ', 6);
          }
          $ret_val .= '- ' . $path . ': ' . $reason;
        }
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
    return dt('No known vulnerabilities were detected in the menu_router table.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultWarn().
   */
  public function getResultWarn() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getAction().
   */
  public function getAction() {
    if ($this->score == SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL) {
      return dt('Delete the offending entries from your menu_router, delete the target file, update your Drupal site code, and check your entire codebase for questionable code using a tool like the Hacked! module.');
    }
  }

  /**
   * Implements \SiteAudit\Check\Abstract\calculateScore().
   */
  public function calculateScore() {
    // DRUPAL SA-CORE-2014-005 Exploits.
    // Example from https://gist.github.com/joshkoenig/ on 2014-10-17.
    $columns = array(
      'access',
      'page',
      'title',
      'theme',
    );
    $sql_query = 'SELECT path';
    foreach ($columns as $column) {
      $sql_query .= ', ' . $column . '_callback, ';
      $sql_query .= $column . '_arguments ';
    }
    $sql_query .= 'FROM {menu_router} ';
    $sql_query .= 'WHERE ';
    $callback_sql = array();
    foreach ($columns as $column) {
      $callback_sql[] = $column . '_callback IN (:names) ';
    }
    $sql_query .= implode('OR ', $callback_sql);

    $result = db_query($sql_query, array(
      ':names' => array(
        'file_put_contents',
      ),
    ));
    if (!$result->rowCount()) {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS;
    }
    foreach ($result as $row) {
      foreach ($columns as $column) {
        $callback = $column . '_callback';
        $arguments = $column . '_arguments';
        if ($row->$callback) {
          $this->registry['menu_router'][$row->path] = $callback . ' - write a file (file_put_contents) with the following value: "' . check_plain($row->$arguments) . '"';
        }
      }
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL;
  }
}
