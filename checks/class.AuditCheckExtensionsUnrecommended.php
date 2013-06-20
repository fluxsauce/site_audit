<?php

class AuditCheckExtensionsUnrecommended extends AuditCheck {
  protected $_extensions = array();

  public function getLabel() {
    return dt('Not recommended');
  }

  public function getResultFail() {
    $ret_val = dt('The following unrecommended modules(s) are currently enabled: @list', array(
      '@list' => implode(', ', array_keys($this->_extensions)),
    ));
    if (drush_get_context('DRUSH_VERBOSE')) {
      if (drush_get_option('html')) {
        $this->html = TRUE;
        $ret_val = '<p>' . $ret_val . '</p>';
        $ret_val .= '<table>';
        $ret_val .= '<thead><tr><th>Name</th><th>Reason</th></thead>';
        $ret_val .= '<tbody>';
        foreach ($this->_extensions as $row) {
          $ret_val .= '<tr><td>' . implode('</td><td>', $row) . '</td></tr>';
        }
        $ret_val .= '</tbody>';
        $ret_val .= '</table>';
      }
      else {
        $ret_val .= PHP_EOL;
        foreach ($this->_extensions as $row) {
          $ret_val .= '    ' . $row[0] . ': ' . $row[1] . PHP_EOL;
        }
      }
    }
    return $ret_val;
  }

  public function getResultInfo() {}

  public function getResultPass() {
    return dt('No unrecommended extensions were detected; no action required.');
  }

  public function getResultWarning() {}

  public function getAction() {
    return dt('Disable and completely remove unrecommended modules for increased performance, stability and security in the Live (production) environment.');
  }

  public function getDescription() {
    return dt('Check for unrecommended modules.');
  }

  public function getScore() {
    $extension_info = drush_get_extensions(FALSE);
    uasort($extension_info, '_drush_pm_sort_extensions');
    $unrecommended_extensions = $this->getExtensions();

    foreach ($extension_info as $key => $extension) {
      $row = array();

      if (!array_key_exists($extension->name, $unrecommended_extensions)) {
        unset($extension_info[$key]);
        continue;
      }

      // Name.
      $row[] = $extension->label;
      // Reason.
      $row[] = $unrecommended_extensions[$extension->name];

      $this->_extensions[$extension->name] = $row;
    }

    if (!empty($this->_extensions)) {
      return AuditCheck::AUDIT_CHECK_SCORE_FAIL;
    }
    return AuditCheck::AUDIT_CHECK_SCORE_PASS;
  }

  /**
   * Get a list of unrecommended extension names and reasons.
   * @return array
   */
  public function getExtensions() {
    $unrecommended_modules = array(
      'honeypot' => dt('honeypot can prevent Varnish caching, which degrades site performance; see http://drupal.org/node/1982848'),
      'fast_404' => dt('Can conflict with Varnish caching; use Drupal\'s 404_fast_html instead'),
      'views_php' => dt('Unfinished and incomplete, Views PHP permits executable code to be stored in the database with no revisioning; a typo introduced in the Views UI can bring down an entire production site with no accountability. See http://api.drupal.org/api/views for details on how to implement your own custom Views functionality.'),
      'views_customfield' => dt('Views Custom Field contains the field for PHP code, which permits executable code to be stored in the database with no revisioning; a typo introduced in the Views UI can bring down an entire production site with no accountability. See http://api.drupal.org/api/views for details on how to implement your own custom Views functionality.'),
      'bad_judgement' => dt('Joke module, framework for anarchy.'),
      'misery' => dt('Joke module, degrades site performance.'),
    );
    if (drush_get_option('vendor') == 'pantheon') {
      $pantheon_unrecommended_modules = array(
        'memcache' => dt('Pantheon does provide memcache support; redis is provided as a service to all customers; see http://helpdesk.getpantheon.com/customer/portal/articles/401317-redis'),
        'memcache_storage' => dt('Pantheon does provide memcache support; redis is provided as a service to all customers; see http://helpdesk.getpantheon.com/customer/portal/articles/401317-redis'),
        // Backup & Migrate and related modules.
        'backup_migrate' => dt('This module makes your Drupal site work harder and degrades site performance; instead, use Pantheon\'s Backup through the site dashboard, which won\'t affect site performance.'),
        'backup_migrate_files' => dt('This module makes your Drupal site work harder and degrades site performance; instead, use Pantheon\'s Backup through the site dashboard, which won\'t affect site performance.'),
        'backup_migrate_prune' => dt('This module makes your Drupal site work harder and degrades site performance; instead, use Pantheon\'s Backup through the site dashboard, which won\'t affect site performance.'),
        'backup_migrate_sftp' => dt('This module makes your Drupal site work harder and degrades site performance; instead, use Pantheon\'s Backup through the site dashboard, which won\'t affect site performance.'),
        'backup_migrate_dropbox' => dt('This module makes your Drupal site work harder and degrades site performance; instead, use Pantheon\'s Backup through the site dashboard, which won\'t affect site performance.'),
        'backup_migrate_cloudfiles' => dt('This module makes your Drupal site work harder and degrades site performance; instead, use Pantheon\'s Backup through the site dashboard, which won\'t affect site performance.'),
        'hpcloud' => dt('This module makes your Drupal site work harder and degrades site performance; instead, use Pantheon\'s Backup through the site dashboard, which won\'t affect site performance.'),
        'nodesquirrel' => dt('This module makes your Drupal site work harder and degrades site performance; instead, use Pantheon\'s Backup through the site dashboard, which won\'t affect site performance.'),
      );
      $unrecommended_modules = array_merge($unrecommended_modules, $pantheon_unrecommended_modules);
    }
    return $unrecommended_modules;
  }
}