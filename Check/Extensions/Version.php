<?php
/**
 * @file
 * Contains \SiteAudit\Check\Extensions\Version.
 */

class SiteAuditCheckExtensionsVersion extends SiteAuditCheckAbstract {
  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Compatibility');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Check for specific versions of modules with known compatibility problems.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {
    $ret_val = dt('The following modules(s) are known to have issues with the specific version installed: @list', array(
      '@list' => implode(', ', array_keys($this->registry['extensions_version'])),
    ));
    if (drush_get_option('detail')) {
      if (drush_get_option('html')) {
        $ret_val .= '<br/>';
        $ret_val .= '<table class="table table-condensed">';
        $ret_val .= '<thead><tr><th>Name</th><th>Reason</th></thead>';
        $ret_val .= '<tbody>';
        foreach ($this->registry['extensions_version'] as $row) {
          $ret_val .= '<tr><td>' . implode('</td><td>', $row) . '</td></tr>';
        }
        $ret_val .= '</tbody>';
        $ret_val .= '</table>';
      }
      else {
        foreach ($this->registry['extensions_version'] as $row) {
          $ret_val .= PHP_EOL;
          if (!drush_get_option('json')) {
            $ret_val .= str_repeat(' ', 6);
          }
          $ret_val .= '- ' . $row[0] . ': ' . $row[1];
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
    return dt('No modules with specific version problems were detected; no action required.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultWarn().
   */
  public function getResultWarn() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getAction().
   */
  public function getAction() {
    if ($this->getScore() != SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS) {
      return dt('Update the modules in question to the latest known good version.');
    }
  }

  /**
   * Implements \SiteAudit\Check\Abstract\calculateScore().
   */
  public function calculateScore() {
    $this->registry['extensions_version'] = array();
    $extension_info = drush_get_extensions(FALSE);
    uasort($extension_info, '_drush_pm_sort_extensions');
    $compatibility_modules = $this->getExtensions();

    foreach ($extension_info as $extension) {
      if (array_key_exists($extension->name, $compatibility_modules)) {
        foreach ($compatibility_modules[$extension->name] as $version) {
          if (version_compare($extension->info['version'], $version['version'], $version['operator'])) {
            $this->registry['extensions_version'][$extension->name] = array(
              $extension->name . ' (' . $extension->info['version'] . ')',
              $version['reason'],
            );
          }
        }
      }
    }

    if (!empty($this->registry['extensions_version'])) {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL;
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS;
  }

  /**
   * Get a list of modules with known compatibility problems.
   * @return array
   *   An array keyed by module name with the keys operator for comparison,
   *   version for the version to compare and reason to describe why there is a
   *   compatiability problem.
   */
  public function getExtensions() {
    $compatibility_modules = array(
      'redis' => array(
        array(
          'operator' => '<',
          'version' => '7.x-2.6',
          'reason' => dt('Versions prior to v7.x-2.6 do not properly clear cache; upgrade to 2.6 or above.'),
        ),
      ),
    );
    if (drush_get_option('vendor') == 'pantheon') {
      $pantheon_compatibility_modules = array(
        'pantheon_apachesolr' => array(
          array(
            'operator' => '<',
            'version' => '7.x-1.0',
            'reason' => dt("Versions prior to v7.x-1.0 are not compatible with Pantheon's indexing service. Upgrade Drupal core."),
          ),
        ),
      );
      $compatibility_modules = array_merge($compatibility_modules, $pantheon_compatibility_modules);
    }
    return $compatibility_modules;
  }
}
