<?php
/**
 * @file
 * Contains \SiteAudit\Check\Extensions\Duplicate.
 */

class SiteAuditCheckExtensionsDuplicate extends SiteAuditCheckAbstract {
  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Duplicates');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Check for duplicate extensions in the site codebase.');
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
    return dt('No duplicate extensions were detected.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultWarn().
   */
  public function getResultWarn() {
    $ret_val = dt('The following duplicate extensions were found:');
    if (drush_get_option('html')) {
      $ret_val = '<p>' . $ret_val . '</p>';
      $ret_val .= '<table class="table table-condensed">';
      $ret_val .= '<thead><tr><th>Name</th><th>Paths</th></thead>';
      $ret_val .= '<tbody>';
      foreach ($this->registry['extensions_dupe'] as $name => $paths) {
        $ret_val .= '<tr><td>' . $name . '</td>';
        $ret_val .= '<td>' . implode('<br/>', $paths) . '</td></tr>';
      }
      $ret_val .= '</tbody>';
      $ret_val .= '</table>';
    }
    else {
      foreach ($this->registry['extensions_dupe'] as $name => $paths) {
        $ret_val .= PHP_EOL;
        if (!drush_get_option('json')) {
          $ret_val .= str_repeat(' ', 6);
        }
        $ret_val .= $name . PHP_EOL;
        $extension_list = '';
        foreach ($paths as $path) {
          $extension_list .= str_repeat(' ', 8) . $path . PHP_EOL;
        }
        $ret_val .= rtrim($extension_list);
      }
    }
    return $ret_val;
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getAction().
   */
  public function getAction() {
    if ($this->score != SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS) {
      return dt('Prune your codebase to have only one copy of any given extension. If you are using an installation profile, work with the maintainer to update the relevant modules. If you remove an enabled module, you may have to rebuild the registry.');
    }
  }

  /**
   * Implements \SiteAudit\Check\Abstract\calculateScore().
   */
  public function calculateScore() {
    $this->registry['extensions_dupe'] = array();
    $drupal_root = drush_get_context('DRUSH_SELECTED_DRUPAL_ROOT');
    $command = "find $drupal_root -xdev -type f -name '*.info' -o -path './" . variable_get('file_public_path', conf_path() . '/files') . "' -prune";
    exec($command, $result);
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
      $path = substr($path, strlen($drupal_root) + 1);
      $info = file($drupal_root . '/' . $path);
      foreach ($info as $line) {
        if (strpos($line, 'version') === 0) {
          $version = explode('=', $line);
          if (isset($version[1])) {
            $path .= ' (' . trim(str_replace('"', '', $version[1])) . ')';
          }
        }
      }
      $this->registry['extensions_dupe'][$name][] = $path;
    }

    // Review the detected extensions.
    foreach ($this->registry['extensions_dupe'] as $extension => $paths) {
      // No duplicates.
      if (count($paths) == 1) {
        unset($this->registry['extensions_dupe'][$extension]);
        continue;
      }

      // If every path is within an installation profile, ignore.
      $paths_in_profile = 0;
      foreach ($paths as $path) {
        if (strpos($path, 'profiles/') === 0) {
          $paths_in_profile++;
        }
      }
      if ($paths_in_profile == count($paths)) {
        unset($this->registry['extensions_dupe'][$extension]);
        continue;
      }
    }

    // Determine score.
    if (count($this->registry['extensions_dupe'])) {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN;
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS;
  }
}
