<?php
/**
 * @file
 * Contains \AuditCheckExtensionsDuplicate.
 */

class AuditCheckExtensionsDuplicate extends AuditCheck {
  /**
   * Implements \AuditCheck\getLabel().
   */
  public function getLabel() {
    return dt('Count');
  }

  /**
   * Implements \AuditCheck\getDescription().
   */
  public function getDescription() {
    return dt('Check for duplicate extensions in the site codebase.');
  }

  /**
   * Implements \AuditCheck\getResultFail().
   */
  public function getResultFail() {}

  /**
   * Implements \AuditCheck\getResultInfo().
   */
  public function getResultInfo() {}

  /**
   * Implements \AuditCheck\getResultPass().
   */
  public function getResultPass() {
    return dt('No duplicate extensions were detected.');
  }

  /**
   * Implements \AuditCheck\getResultWarning().
   */
  public function getResultWarning() {
    $ret_val = dt('The following duplicate extensions were detected:');
    if ($this->html) {
      $ret_val = '<p>' . $ret_val . '</p>';
      $ret_val .= '<table>';
      $ret_val .= '<thead><tr><th>Name</th><th>Paths</th></thead>';
      $ret_val .= '<tbody>';
      foreach ($this->registry['extensions_dupe'] as $name => $paths) {
        if (count($paths) > 1) {
          $ret_val .= '<tr><td>' . $name . '</td>';
          $ret_val .= '<td>' . implode('<br/>', $paths) . '</td></tr>';
        }
      }
      $ret_val .= '</tbody>';
      $ret_val .= '</table>';
    }
    else {
      $ret_val .= PHP_EOL;
      foreach ($this->registry['extensions_dupe'] as $name => $paths) {
        if (count($paths) > 1) {
          $ret_val .= '    ' . $name . PHP_EOL;
          foreach ($paths as $path) {
            $ret_val .= '      ' . $path . PHP_EOL;
          }
        }
      }
    }
    return $ret_val;
  }

  /**
   * Implements \AuditCheck\getAction().
   */
  public function getAction() {
    if ($this->score != AuditCheck::AUDIT_CHECK_SCORE_PASS) {
      return dt('Prune your codebase to have only one copy of any given extension. If you are using an installation profile, work with the maintainer to update the relevant modules. If you remove an enabled module, you may have to rebuild the registry.');
    }
  }

  /**
   * Implements \AuditCheck\getScore().
   */
  public function getScore() {
    $this->registry['extensions_dupe'] = array();
    $warn = FALSE;
    $drupal_root = drush_get_context('DRUSH_SELECTED_DRUPAL_ROOT');
    exec("find $drupal_root -type f -name '*.info' -o -path './sites/default/files' -prune -xdev", $result);
    foreach ($result as $path) {
      $name = substr(array_pop(explode('/', $path)), 0, -5);
      // Safe duplicates.
      if (in_array($name, array(
        'drupal_system_listing_compatible_test',
        'drupal_system_listing_incompatible_test',
      ))) {
        continue;
      }
      if (!isset($this->registry['extensions_dupe'][$name])) {
        $this->registry['extensions_dupe'][$name] = array();
      }
      $this->registry['extensions_dupe'][$name][] = substr($path, strlen($drupal_root) + 1);
      if (count($this->registry['extensions_dupe'][$name]) > 1) {
        $warn = TRUE;
      }
    }
    if ($warn) {
      return AuditCheck::AUDIT_CHECK_SCORE_WARN;
    }
    return AuditCheck::AUDIT_CHECK_SCORE_PASS;
  }
}
