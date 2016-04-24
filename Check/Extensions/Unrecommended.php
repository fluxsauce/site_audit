<?php
/**
 * @file
 * Contains \SiteAudit\Check\Extensions\Unrecommended.
 */

/**
 * Class SiteAuditCheckExtensionsUnrecommended.
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
        $ret_val .= '<thead><tr><th>' . dt('Name') . '</th><th>' . dt('Reason') . '</th></thead>';
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

      // Not in the list of known unrecommended modules.
      if (!array_key_exists($extension->name, $unrecommended_extensions)) {
        continue;
      }

      $in_profile = (strpos($extension->filename, 'profiles/') === 0);
      $status = drush_get_extension_status($extension);

      // If in profiles and disabled, ignore.
      if ($in_profile && $status != 'enabled') {
        continue;
      }

      // Special check for APC; if PHP 5.5 and above, allow.
      if ($extension->name == 'apc' && version_compare(phpversion(), '5.5.0', '>=')) {
        continue;
      }

      // Name.
      $row[] = $extension->label;
      // Reason.
      $row[] = $unrecommended_extensions[$extension->name];

      $this->registry['extensions_unrec'][$extension->name] = $row;
    }

    if (!empty($this->registry['extensions_unrec'])) {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL;
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS;
  }

  /**
   * Get a list of unrecommended extension names and reasons.
   *
   * @return array
   *   Keyed by module machine name, value is explanation.
   */
  public function getExtensions() {
    $unrecommended_modules = array(
      'apc' => dt("APC takes away space for PHP's opcode cache, potentially degrading performance for high traffic and complex sites. Use redis or another similar caching backend."),
      'fast_404' => dt("Can cause 404s to be cached by Varnish; use Drupal's 404_fast_html instead"),
      'views_php' => dt('Unfinished and incomplete, Views PHP permits executable code to be stored in the database with no revisioning; a typo introduced in the Views UI can bring down an entire production site with no accountability. See http://api.drupal.org/api/views for details on how to implement your own custom Views functionality.'),
      'views_customfield' => dt('Views Custom Field contains the field for PHP code, which permits executable code to be stored in the database with no revisioning; a typo introduced in the Views UI can bring down an entire production site with no accountability. See http://api.drupal.org/api/views for details on how to implement your own custom Views functionality.'),
      'bad_judgement' => dt('Joke module, framework for anarchy.'),
      'misery' => dt('Joke module, degrades site performance.'),
      'supercron' => dt('Abandoned due to security concerns. https://drupal.org/node/1401644'),
    );

    if (drush_get_option('vendor') == 'acquia') {
      $acquia_unrecommended_modules = array(
        'pantheon_apachesolr' => dt('The Pantheon Solr integration does not work on Acquia.'),
        'pantheon_api' => dt('The Pantheon API does not work on Acquia.'),
        'pantheon_login' => dt('The Pantheon login integration does not work on Acquia.'),
        'redis' => dt('Acquia does not provide redis; instead, Memcached is provided as a service; see https://docs.acquia.com/cloud/performance/memcached'),
      );
      $unrecommended_modules = array_merge($unrecommended_modules, $acquia_unrecommended_modules);
    }

    if (drush_get_option('vendor') == 'pantheon') {
      $pantheon_unrecommended_modules = array(
        'memcache' => dt('Pantheon does not provide memcache; instead, redis is provided as a service; see http://helpdesk.getpantheon.com/customer/portal/articles/401317'),
        'memcache_storage' => dt('Pantheon does not provide memcache; instead, redis is provided as a service to all customers; see http://helpdesk.getpantheon.com/customer/portal/articles/401317'),
        'drupal_less' => dt('Before deployment, compile and commit CSS.'),
        'boost' => dt("Boost is optimal for shared hosts; Pantheon's Varnish caching layer handles anonymous page caching more efficiently."),
      );
      $unrecommended_modules = array_merge($unrecommended_modules, $pantheon_unrecommended_modules);
    }

    return $unrecommended_modules;
  }

}
