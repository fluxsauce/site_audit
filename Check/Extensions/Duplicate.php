<?php
/**
 * @file
 * Contains \SiteAudit\Check\Extensions\Duplicate.
 */

/**
 * Class SiteAuditCheckExtensionsDuplicate.
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
      $ret_val .= '<thead><tr><th>' . dt('Name') . '</th><th>' . dt('Paths') . '</th></thead>';
      $ret_val .= '<tbody>';
      foreach ($this->registry['extensions_dupe'] as $name => $extension_infos) {
        $ret_val .= '<tr><td>' . $name . '</td>';
        $paths = array();
        foreach ($extension_infos as $extension_info) {
          $extension = $extension_info['path'];
          if ($extension_info['version']) {
            $extension .= ' (' . $extension_info['version'] . ')';
          }
          $paths[] = $extension;
        }
        $ret_val .= '<td>' . implode('<br/>', $paths) . '</td></tr>';
      }
      $ret_val .= '</tbody>';
      $ret_val .= '</table>';
    }
    else {
      foreach ($this->registry['extensions_dupe'] as $name => $extension_infos) {
        $ret_val .= PHP_EOL;
        if (!drush_get_option('json')) {
          $ret_val .= str_repeat(' ', 6);
        }
        $ret_val .= $name . PHP_EOL;
        $extension_list = '';
        foreach ($extension_infos as $extension_info) {
          $extension_list .= str_repeat(' ', 8);
          $extension_list .= $extension_info['path'];
          if ($extension_info['version']) {
            $extension_list .= ' (' . $extension_info['version'] . ')';
          }
          $extension_list .= PHP_EOL;
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
      $path_parts = explode('/', $path);
      $name = substr(array_pop($path_parts), 0, -5);
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

      $extension_info = array(
        'path' => substr($path, strlen($drupal_root) + 1),
        'version' => NULL,
      );
      $info = file($drupal_root . '/' . $extension_info['path']);
      foreach ($info as $line) {
        if (strpos($line, 'version') === 0) {
          $version = explode('=', $line);
          if (isset($version[1])) {
            $extension_info['version'] = trim(str_replace('"', '', $version[1]));
          }
        }
      }
      $this->registry['extensions_dupe'][$name][] = $extension_info;
    }

    // Review the detected extensions.
    foreach ($this->registry['extensions_dupe'] as $extension_name => $extension_infos) {
      // No duplicates.
      if (count($extension_infos) == 1) {
        unset($this->registry['extensions_dupe'][$extension_name]);
        continue;
      }

      // If every path is within an installation profile, ignore.
      $paths_in_profile = 0;
      foreach ($extension_infos as $index => $extension_info) {
        if (strpos($extension_info['path'], 'profiles/') === 0) {
          $paths_in_profile++;
        }
      }
      if ($paths_in_profile == count($extension_infos)) {
        unset($this->registry['extensions_dupe'][$extension_name]);
        continue;
      }

      // Allow overrides of installation profile extensions.
      $extension_object = $this->registry['extensions'][$extension_name];
      if (
        // The enabled extension has version info.
        isset($extension_object->info['version'])
        && $extension_object->info['version']
        // There is a version of the extension in an installation profile.
        && $paths_in_profile
        // The extension in question is enabled.
        && drush_get_extension_status($extension_object) == 'enabled'
        // The enabled extension is not in profiles.
        && strpos($extension_object->uri, 'profiles/') === FALSE
      ) {
        $skip = TRUE;
        foreach ($extension_infos as $extension_info) {
          // Not within the profile and there's version information.
          if (strpos($extension_info['path'], 'profiles/') !== FALSE && $extension_info['version']) {
            // If the installed version is equal or newer to the enabled.
            if (version_compare($extension_object->info['version'], $extension_info['version']) < 1) {
              $skip = FALSE;
              break;
            }
          }
        }
        if ($skip === TRUE) {
          unset($this->registry['extensions_dupe'][$extension_name]);
        }
      }
    }

    // Determine score.
    if (count($this->registry['extensions_dupe'])) {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN;
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS;
  }

}
