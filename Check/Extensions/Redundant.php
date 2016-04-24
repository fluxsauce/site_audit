<?php
/**
 * @file
 * Contains \SiteAudit\Check\Extensions\Redundant.
 */

/**
 * Class SiteAuditCheckExtensionsRedundant.
 */
class SiteAuditCheckExtensionsRedundant extends SiteAuditCheckAbstract {

  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Redundant');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Check for redundant modules.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {
    $ret_val = dt('The following redundant modules(s) currently exist in your codebase: @list', array(
      '@list' => implode(', ', array_keys($this->registry['extensions_redundant'])),
    ));
    if (drush_get_option('detail')) {
      if (drush_get_option('html')) {
        $ret_val .= '<br/>';
        $ret_val .= '<table class="table table-condensed">';
        $ret_val .= '<thead><tr><th>' . dt('Name') . '</th><th>' . dt('Reason') . '</th></thead>';
        $ret_val .= '<tbody>';
        foreach ($this->registry['extensions_redundant'] as $row) {
          $ret_val .= '<tr><td>' . implode('</td><td>', $row) . '</td></tr>';
        }
        $ret_val .= '</tbody>';
        $ret_val .= '</table>';
      }
      else {
        foreach ($this->registry['extensions_redundant'] as $row) {
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
    return dt('No redundant modules were detected; no action required.');
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
      return dt('Standardize on the recommended module and remove the redundant module(s) from your codebase.');
    }
  }

  /**
   * Implements \SiteAudit\Check\Abstract\calculateScore().
   */
  public function calculateScore() {
    $this->registry['extensions_redundant'] = array();
    $extension_info = $this->registry['extensions'];
    uasort($extension_info, '_drush_pm_sort_extensions');
    $redundant_extensions = $this->getExtensions();

    foreach ($extension_info as $extension) {
      $row = array();

      // Not in the list of known redundant modules.
      if (!array_key_exists($extension->name, $redundant_extensions)) {
        continue;
      }

      $in_profile = (strpos($extension->filename, 'profiles/') === 0);
      $status = drush_get_extension_status($extension);

      // If in profiles and disabled, ignore.
      if ($in_profile && $status != 'enabled') {
        continue;
      }

      // Name.
      $row[] = $extension->label;
      // Reason.
      $row[] = $redundant_extensions[$extension->name];

      $this->registry['extensions_redundant'][$extension->name] = $row;
    }

    if (!empty($this->registry['extensions_redundant'])) {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL;
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS;
  }

  /**
   * Get a list of redundant extension names and reasons.
   *
   * @return array
   *   Keyed by module machine name, value is explanation.
   */
  public function getExtensions() {
    $redundant_modules = array(
      'metatags_quick' => dt('Standardize on https://www.drupal.org/project/metatag'),
      'page_title' => dt('Standardize on https://www.drupal.org/project/metatag'),
      'opengraph_meta' => dt('Standardize on https://www.drupal.org/project/metatag'),
      'metatag_itunes' => dt('Standardize on https://www.drupal.org/project/metatag'),
      'context_metadata' => dt('Standardize on https://www.drupal.org/project/metatag'),
      'context_meta' => dt('Standardize on https://www.drupal.org/project/metatag'),
      'smart_app_banners' => dt('Standardize on https://www.drupal.org/project/metatag'),
    );

    return $redundant_modules;
  }

}
