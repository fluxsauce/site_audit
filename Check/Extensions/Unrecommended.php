<?php
/**
 * @file
 * Contains \SiteAudit\Check\Extensions\Unrecommended.
 */

class SiteAuditCheckExtensionsUnrecommended extends SiteAuditCheckAbstract {
  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Not recommended');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Check for unrecommended modules.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {
    $ret_val = dt('The following unrecommended modules(s) currently exist in your codebase: @list', array(
      '@list' => implode(', ', array_keys($this->registry['extensions_unrec'])),
    ));
    if (drush_get_option('detail')) {
      if (drush_get_option('html')) {
        $ret_val .= '<br/>';
        $ret_val .= '<table class="table table-condensed">';
        $ret_val .= '<thead><tr><th>Name</th><th>Reason</th></thead>';
        $ret_val .= '<tbody>';
        foreach ($this->registry['extensions_unrec'] as $row) {
          $ret_val .= '<tr><td>' . implode('</td><td>', $row) . '</td></tr>';
        }
        $ret_val .= '</tbody>';
        $ret_val .= '</table>';
      }
      else {
        foreach ($this->registry['extensions_unrec'] as $row) {
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
    return dt('No unrecommended extensions were detected; no action required.');
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
      return dt('Disable and completely remove unrecommended modules from your codebase for increased performance, stability and security in the any environment.');
    }
  }

  /**
   * Implements \SiteAudit\Check\Abstract\calculateScore().
   */
  public function calculateScore() {
    $this->registry['extensions_unrec'] = array();
    $extension_info = $this->registry['extensions'];
    uasort($extension_info, '_drush_pm_sort_extensions');
    $unrecommended_extensions = $this->getExtensions();

    foreach ($extension_info as $extension) {
      $row = array();

      $machineName = $extension->getName();

      // Get the human readable name of the extension
      $humanReadableName = '';
      if (isset($extension->info['name'])) {
        $humanReadableName = $extension->info['name'];
      }
      else {
        $humanReadableName = $machineName;
      }

      // Construct label from machine name and human readable name of the extension
      $label = $humanReadableName . ' (' . $machineName . ')';
      // Not in the list of known unrecommended modules.
      if (!array_key_exists($machineName, $unrecommended_extensions)) {
        continue;
      }

      // Name.
      $row[] = $label;
      // Reason.
      $row[] = $unrecommended_extensions[$machineName];

      $this->registry['extensions_unrec'][$machineName] = $row;
    }

    if (!empty($this->registry['extensions_unrec'])) {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL;
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS;
  }

  /**
   *  Get a list of unrecommended extension names and reasons.
   *
   *  @return array
   *    Keyed by module machine name, value is explanation.
   */
  public function getExtensions() {
    $unrecommended_modules = array(
      'bad_judgement' => dt('Joke module, framework for anarchy.'),
      'php' => dt('Executable code should never be stored in the database.'),
    );
    return $unrecommended_modules;
  }
}
