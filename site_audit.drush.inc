<?php
/**
 * @file
 * Drupal site auditing commands.
 */

use Drupal\Core\Url;

// Avoid loading multiple versions of site_audit.
if (class_exists('SiteAuditReportAbstract')) {
  return;
}

// Major version check.
if (version_compare(phpversion(), '5.3.0') < 0) {
  return drush_set_error('SITE_AUDIT_REQUIREMENTS_ERROR', dt('site_audit requires command-line PHP 5.3.0 or higher; support for PHP 5.3 was terminated August 14, 2014, and PHP 5.2 was terminated January 6, 2011. If your host is unable or unwilling to upgrade to a major supported version, consider an alternative.'));
}

// Includes.
define('SITE_AUDIT_BASE_PATH', __DIR__);
require_once SITE_AUDIT_BASE_PATH . '/site_audit.autoload.inc';
require_once SITE_AUDIT_BASE_PATH . '/Report/Abstract.php';
require_once SITE_AUDIT_BASE_PATH . '/Check/Abstract.php';

/**
 * Get common command options used by all site_audit commands.
 *
 * @return array
 *   Associative array of common command options.
 */
function site_audit_common_options() {
  return array(
    'html' => array(
      'description' => dt('If set, render as HTML report.'),
    ),
    'json' => array(
      'description' => dt('If set, render as JSON report.'),
    ),
    'detail' => array(
      'description' => dt('If set, provided detailed responses.'),
    ),
    'skip' => array(
      'description' => dt('Name(s) of reports or specific checks to skip, comma separated.'),
      'example-value' => dt('insights,ExtensionsDev'),
      'value' => 'required',
    ),
    'bootstrap' => array(
      'description' => dt('If enabled, will wrap the generated report with Twitter Bootstrap 3.0 inline HTML.'),
    ),
  );
}

/**
 * Implements hook_drush_command().
 */
function site_audit_drush_command() {
  $items = array();

  $vendor_options = array(
    'vendor' => array(
      'description' => dt("If used, will adjust recommendations based on a Vendor's specific needs. Currently supported: pantheon"),
      'example-value' => dt('pantheon'),
      'value' => 'required',
    ),
  );

  $options_all = array_merge(site_audit_common_options(), $vendor_options);

  $items['audit-best-practices'] = array(
    'description' => dt('Audit best practices used.'),
    'callback' => 'drush_site_audit_generic_callback',
    'aliases' => array('abp'),
    'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_FULL,
    'options' => array_merge(site_audit_common_options(), $vendor_options),
    'checks' => array(
      'Fast404',
      'Sites',
      'Multisite',
      'Settings',
      'Services',
      'SitesDefault',
      'SitesSuperfluous',
      'FolderStructure',
    ),
  );

  $items['audit-cache'] = array(
    'description' => dt("Audit Drupal's caching settings."),
    'aliases' => array('ac'),
    'callback' => 'drush_site_audit_generic_callback',
    'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_FULL,
    'options' => array_merge(site_audit_common_options(), $vendor_options),
    'checks' => array(
      'AllBackends',
      'DefaultBackends',
      'UsedBackends',
      'PageExpire',
      'PreprocessCss',
      'PreprocessJs',
    ),
  );

  $options_codebase = array(
    'custom-code' => array(
      'description' => dt('Comma separated the directories/files containing custom code.'),
      'example-value' => dt('./modules/custom,./modules/features,./code.php'),
      'value' => 'required',
    ),
    'phploc-names' => array(
      'description' => dt('A comma-separated list of file names for phploc to check'),
      'example-value' => dt('*.php,*.module,*.install,*.test,*.inc,*.profile,*.theme'),
      'value' => 'required',
    ),
    'phploc-names-exclude' => array(
      'description' => dt('A comma-separated list of file names for phploc to exclude'),
      'example-value' => dt('*.features.*,*_default.inc,*.ds.inc,*.strongarm.inc,*.panelizer.inc,*_defaults.inc,*.box.inc,*.context.inc,*displays.inc'),
      'value' => 'required',
    ),
    'phpmd-ruleset' => array(
      'description' => dt('phpmd rules to be used, can also be an xml file with custom ruleset'),
      'example-value' => dt('codesize,naming,design,unusedcode'),
      'value' => 'required',
    ),
    'phpmd-suffixes' => array(
      'description' => dt('Comma-separated string of valid source code filename extensions checked by phpmd'),
      'example-value' => dt('.php,.module,.install,.test,.inc,.profile,.theme'),
      'value' => 'required',
    ),
    'phpmd-exclude' => array(
      'description' => dt('Comma-separated string of patterns that are used to ignore directories by phpmd'),
      'example-value' => dt('*.features.*,*_default.inc,*.ds.inc,*.strongarm.inc,*.panelizer.inc,*_defaults.inc,*.box.inc,*.context.inc,*displays.inc'),
      'value' => 'required',
    ),
    'phpmd-minimumpriority' => array(
      'description' => dt('The phpmd rule priority threshold; rules with lower priority than they will not be used; between 1 and 5'),
      'example-value' => dt('3'),
      'value' => 'required',
    ),
    'phpmd-strict' => array(
      'description' => dt('Phpmd also reports those nodes with a @SuppressWarnings annotation'),
      'value' => 'optional',
    ),
    'phpcpd-names' => array(
      'description' => dt('A comma-separated list of file names for phpcpd to check'),
      'example-value' => dt('*.php,*.module,*.install,*.test,*.inc,*.profile,*.theme'),
      'value' => 'required',
    ),
    'phpcpd-names-exclude' => array(
      'description' => dt('A comma-separated list of file names for phpcpd to exclude'),
      'example-value' => dt('*.features.*,*_default.inc,*.ds.inc,*.strongarm.inc,*.panelizer.inc,*_defaults.inc,*.box.inc,*.context.inc,*displays.inc'),
      'value' => 'required',
    ),
    'phpdcd-names' => array(
      'description' => dt('A comma-separated list of file names for phpdcd to check'),
      'example-value' => dt('*.php,*.module,*.install,*.test,*.inc,*.profile,*.theme'),
      'value' => 'required',
    ),
    'phpdcd-names-exclude' => array(
      'description' => dt('A comma-separated list of file names for phpdcd to exclude'),
      'example-value' => dt('*.features.*,*_default.inc,*.ds.inc,*.strongarm.inc,*.panelizer.inc,*_defaults.inc,*.box.inc,*.context.inc,*displays.inc'),
      'value' => 'required',
    ),

    'phpcs-extensions' => array(
      'description' => dt('A comma separated list of file extensions to be checked by phpcs'),
      'example-value' => dt('php,module,inc,install,test,profile,theme,css,info,txt'),
      'value' => 'required',
    ),
    'phpcs-ignore' => array(
      'description' => dt('A comma separated list of patterns for phpcs to ignore files and directories'),
      'example-value' => dt('*/modules/features/'),
      'value' => 'required',
    ),
    'phpcs-standard' => array(
      'description' => dt('Name or absolute path of the coding standard to be used by phpcs'),
      'example-value' => dt('~/.drush/site_audit/vendor/drupal/coder/coder_sniffer/Drupal'),
      'value' => 'required',
    ),
  );

  $items['audit-codebase'] = array(
    'description' => dt('Audit the codebase.'),
    'aliases' => array('acb'),
    'callback' => 'drush_site_audit_generic_callback',
    'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_FULL,
    'options' => array_merge(site_audit_common_options(), $options_codebase),
    'checks' => array(
      'SizeFiles',
      'SizeAll',
      'ManagedFileCount',
      'ManagedFileSize',
      'GitContributions',
      'PhpLOC',
      'PhpMessDetection',
      'PhpCodeSniffer',
      'PhpCopyPasteDetection',
      'PhpDeadCodeDetection',
    ),
  );

  $items['audit-content'] = array(
    'description' => dt('Audit content.'),
    'aliases' => array('acn'),
    'callback' => 'drush_site_audit_generic_callback',
    'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_FULL,
    'options' => site_audit_common_options(),
    'checks' => array(
      'ContentEntityTypes',
      'ContentEntityTypesUnused',
      'DuplicateTitles',
      'Taxonomy',
      'Vocabularies',
      'VocabulariesUnused',
      'FieldEnabled',
      'FieldCount',
      'FieldInstances',
      'FieldsUnused',
    ),
  );

  $items['audit-cron'] = array(
    'description' => dt('Audit cron.'),
    'aliases' => array('acr'),
    'callback' => 'drush_site_audit_generic_callback',
    'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_FULL,
    'options' => site_audit_common_options(),
    'checks' => array(
      'Enabled',
      'Last',
    ),
  );

  $options_db = array(
    'min_rows' => array(
      'description' => dt('Minimum number of rows to check for. Defaults to 1000.'),
      'example-value' => dt('1000'),
      'value' => 'required',
    ),
  );
  $options_all = array_merge($options_all, $options_db);

  $items['audit-database'] = array(
    'description' => dt("Report information about a site's database."),
    'aliases' => array('ad'),
    'callback' => 'drush_site_audit_generic_callback',
    'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_FULL,
    'options' => array_merge(site_audit_common_options(), $options_db),
    'checks' => array(
      'Size',
      'Collation',
      'Engine',
      'RowCount',
      'Fragmentation',
    ),
  );

  $items['audit-extensions'] = array(
    'description' => dt('Audit extensions (modules and themes).'),
    'aliases' => array('ae'),
    'callback' => 'drush_site_audit_generic_callback',
    'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_FULL,
    'options' => array_merge(site_audit_common_options(), $vendor_options),
    'checks' => array(
      'Count',
      'Dev',
      'Duplicate',
      'Unrecommended',
    ),
  );
  $items['audit-extensions']['options']['extension_count'] = array(
    'description' => dt('Specify threshold for average number of extensions'),
    'example-value' => dt('150'),
    'value' => 'required',
  );

  $options_fe = array(
    'impact' => array(
      'description' => dt('Only show results with an impact greater than what\'s specified. 3 is considered high impact.'),
      'example-value' => dt('3'),
      'value' => 'required',
    ),
    'limit' => array(
      'description' => dt('Limit the number of records shown for each item.'),
      'example-value' => dt('3'),
      'value' => 'required',
    ),
    'wpt-key' => array(
      'description' => dt('WebPageTest API Key - see http://www.webpagetest.org/getkey.php'),
      'example-value' => dt('3'),
      'value' => 'required',
    ),
    'gi-key' => array(
      'description' => dt('Google Code API Key - see https://developers.google.com/speed/docs/insights/v1/getting_started#auth'),
      'value' => 'required',
    ),
    'url' => array(
      'description' => dt('Limit the number of records shown for each item.'),
      'example-value' => dt('3'),
      'value' => 'required',
    ),
  );
  $options_all = array_merge($options_all, $options_fe);
  $items['audit-front-end'] = array(
    'description' => dt("Analyze a site's front end performance."),
    'aliases' => array('afe'),
    'callback' => 'drush_site_audit_generic_callback',
    'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_FULL,
    'options' => array_merge(site_audit_common_options(), $options_fe),
    'checks' => array(
      'GooglePageSpeed',
      'WebPageTest',
    ),
  );

  $items['audit-security'] = array(
    'description' => dt('Audit a site for known security vulnerabilities.'),
    'aliases' => array('asec'),
    'callback' => 'drush_site_audit_generic_callback',
    'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_FULL,
    'options' => site_audit_common_options(),
    'checks' => array(
      'MenuRouter',
    ),
  );

  $items['audit-status'] = array(
    'description' => dt("Audit Drupal's built-in status report."),
    'aliases' => array('as'),
    'callback' => 'drush_site_audit_generic_callback',
    'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_FULL,
    'options' => site_audit_common_options(),
    'checks' => array(
      'System',
    ),
  );

  $items['audit-users'] = array(
    'description' => dt('Audit Users.'),
    'aliases' => array('au'),
    'callback' => 'drush_site_audit_generic_callback',
    'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_FULL,
    'options' => site_audit_common_options(),
    'checks' => array(
      'CountAll',
      'CountBlocked',
      'RolesList',
      'WhoIsNumberOne',
      'BlockedNumberOne',
    ),
  );

  $items['audit-views'] = array(
    'description' => dt('Audit Views.'),
    'aliases' => array('av'),
    'callback' => 'drush_site_audit_generic_callback',
    'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_FULL,
    'options' => site_audit_common_options(),
    'checks' => array(
      'Enabled',
      'Count',
      'CacheResults',
      'CacheOutput',
    ),
  );

  $items['audit-watchdog'] = array(
    'description' => dt('Audit the database logs.'),
    'aliases' => array('aw'),
    'callback' => 'drush_site_audit_generic_callback',
    'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_FULL,
    'options' => array_merge(site_audit_common_options(), $vendor_options),
    'checks' => array(
      'Syslog',
      'Enabled',
      'Count',
      'Age',
      '404',
      'Php',
    ),
  );

  $items['audit-all'] = array(
    'description' => dt('Executes every Site Audit Report'),
    'aliases' => array('aa'),
    'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_FULL,
    'options' => $options_all,
    'reports' => array(
      'BestPractices',
      'Cache',
      'Codebase',
      'Content',
      'Extensions',
      'Cron',
      'Database',
      'Users',
      'FrontEnd',
      'Status',
      'Watchdog',
      'Views',
    ),
  );

  $items['site-audit-version'] = array(
    'description' => dt('Show the Site Audit version.'),
    'aliases' => array('sav'),
    'callback' => 'drush_site_audit_site_audit_version',
    'bootstrap' => DRUSH_BOOTSTRAP_NONE,
  );

  return $items;
}

/**
 * Implements hook_drush_help().
 */
function site_audit_drush_help($section) {
  switch ($section) {
    case 'meta:site_audit:title':
      return dt('Tools for auditing a Drupal site');

  }
}

/**
 * Determine if in a development environment.
 *
 * @return bool
 *   Whether site is in a development environment.
 */
function site_audit_env_is_dev() {
  // Pantheon.
  if (drush_get_option('vendor') == 'pantheon') {
    return (defined('PANTHEON_ENVIRONMENT') && !in_array(PANTHEON_ENVIRONMENT, array('test', 'live')));
  }
  // Your host could be here! Submit an issue and/or a patch.
  return FALSE;
}

/**
 * Display the Site Audit version.
 */
function drush_site_audit_site_audit_version() {
  $info = parse_ini_file(SITE_AUDIT_BASE_PATH . '/site_audit.info');
  drush_print(dt('Site Audit v@site_audit_version', array(
    '@site_audit_version' => $info['site_audit_version'],
  )));
}

/**
 * Check the Drupal major version.
 *
 * @return bool
 *   FALSE if unsupported.
 */
function site_audit_version_check() {
  $drupal_major_version = drush_drupal_major_version();
  if ($drupal_major_version < 8) {
    return drush_set_error('SITE_AUDIT_DRUPAL_8_ONLY', dt('Site Audit 2.x only supports Drupal 8.'));
  }
  return TRUE;
}

/**
 * Generic callback for all commands.
 */
function drush_site_audit_generic_callback() {
  $command = drush_get_context('command');
  $command_parts = explode('-', $command['command']);
  $report_class = 'SiteAuditReport' . implode('', array_map('ucfirst', array_slice($command_parts, 1)));
  $report = new $report_class();
  $report->render();
}

/**
 * Generic validator for all commands.
 */
function drush_site_audit_generic_callback_validate() {
  return site_audit_version_check();
}

/**
 * Validate execution of all reports.
 */
function drush_site_audit_audit_all_validate($url = '', $key = '') {
  return drush_site_audit_generic_callback_validate();
}

/**
 * Execute every single report.
 */
function drush_site_audit_audit_all($url = '', $key = '') {
  $reports_to_skip = array();
  if (drush_get_option('skip')) {
    $reports_to_skip = explode(',', drush_get_option('skip'));
  }

  $command = drush_get_command();
  drush_command_invoke_all_ref('drush_command_alter', $command);

  $report_names = $command['reports'];
  if (!empty($report_names)) {
    $reports_to_render = array();
    foreach ($report_names as $report_name) {
      if (is_array($report_name)) {
        require_once $report_name['location'];
        $report_name = $report_name['name'];
      }
      $report_class = 'SiteAuditReport' . $report_name;
      // Allow a specific report to be skipped.
      if (!in_array(strtolower($report_name), $reports_to_skip)) {
        // Special case for arguments.
        if ($report_name == 'FrontEnd') {
          $report = new $report_class($url, $key);
        }
        else {
          $report = new $report_class();
        }
        $reports_to_render[get_class($report)] = $report;
      }
    }

    if (empty($reports_to_render)) {
      return drush_set_error('SITE_AUDIT_NO_REPORTS', dt('No reports are available!'));
    }

    if (drush_get_option('bootstrap')) {
      echo file_get_contents(SITE_AUDIT_BASE_PATH . '/html/header.html');
    }

    // Header.
    $report_time = dt('Generated on @time', array('@time' => date('r')));

    // Prepare JSON format.
    if (drush_get_option('json')) {
      $report_all = array(
        'time' => time(),
        'reports' => array(),
      );
    }
    // Prepare HTML format.
    elseif (drush_get_option('html')) {
      $site_audit_url = Url::fromUri('https://drupal.org/project/site_audit');
      $report_title = \Drupal::l(dt('Site Audit'), $site_audit_url);
      if ($url) {
        $report_title .= ' ' . dt('report for !url', array(
          '!url' => Url::fromUri($url),
        ));
      }
      echo '<div class="page-header">';
      echo '<h1>' . $report_title . '<br/><small>' . $report_time . '</small></h1>';
      echo '</div>';
    }
    // Prepare Drush format.
    else {
      if ($url) {
        drush_print(dt('https://drupal.org/project/site_audit report for @url', array(
          '@url' => $url,
        )));
      }
      else {
        drush_print(dt('https://drupal.org/project/site_audit report'));
      }
      drush_print($report_time);
      drush_print();
    }

    // Table of Contents; HTML only.
    if (drush_get_option('html') && !drush_get_option('json')) {
      echo '<h2 id="top">' . dt('Summary') . '</h2>';
      $buttons = array();
      foreach ($reports_to_render as $report) {
        $button_text = $report->getLabel();
        if ($report->getPercent() != SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO) {
          $button_text .= ' (' . $report->getPercent() . '%)';
        }
        else {
          $button_text .= ' (' . dt('Info') . ')';
        }
        $buttons[] = \Drupal::l($button_text, Url::fromUserInput('#'), array(
          'fragment' => get_class($report),
          'external' => TRUE,
          'attributes' => array(
            'class' => array(
              'btn',
              'btn-' . $report->getPercentCssClass(),
              'btn-mini',
            ),
            'style' => 'margin-bottom: 5px;',
          ),
        ));
      }
      echo '<p>' . implode('&nbsp;', $buttons) . '</p>';
    }
    // Render reports.
    foreach ($reports_to_render as $report) {
      if (drush_get_option('json')) {
        $report_all['reports'][get_class($report)] = json_decode($report->toJson());
      }
      else {
        $report->render();
      }
      if (drush_get_option('html') && !drush_get_option('json')) {
        echo '<div>' . \Drupal::l(dt('Back to top'), Url::fromUserInput('#'), array(
          'fragment' => 'top',
          'external' => TRUE,
          'attributes' => array(
            'class' => array('btn', 'btn-default', 'btn-mini'),
          ),
        )) . '</div>';
      }
    }
    if (drush_get_option('bootstrap')) {
      echo file_get_contents(SITE_AUDIT_BASE_PATH . '/html/footer.html');
    }
  }

  if (drush_get_option('json')) {
    echo json_encode($report_all);
    return;
  }
}

/**
 * Audit best practices validation.
 */
function drush_site_audit_audit_best_practices_validate() {
  return drush_site_audit_generic_callback_validate();
}

/**
 * Audit cache validation.
 */
function drush_site_audit_audit_cache_validate() {
  return drush_site_audit_generic_callback_validate();
}

/**
 * Audit codebase validation.
 */
function drush_site_audit_audit_codebase_validate() {
  return drush_site_audit_generic_callback_validate();
}

/**
 * Audit content validation.
 */
function drush_site_audit_audit_content_validate() {
  return drush_site_audit_generic_callback_validate();
}

/**
 * Audit cron validation.
 */
function drush_site_audit_audit_cron_validate() {
  return drush_site_audit_generic_callback_validate();
}

/**
 * Audit database validation.
 */
function drush_site_audit_audit_database_validate() {
  return drush_site_audit_generic_callback_validate();
}

/**
 * Audit extensions validation.
 */
function drush_site_audit_audit_extensions_validate() {
  $extension_count = drush_get_option('extension_count');
  if ($extension_count) {
    if ((int) $extension_count != $extension_count || $extension_count < 1) {
      return drush_set_error(dt('The extension count must be a whole number above zero.'));
    }
  }
  return drush_site_audit_generic_callback_validate();
}

/**
 * Audit front end validation.
 */
function drush_site_audit_audit_front_end_validate() {
  // Check for a valid URL.
  $url = drush_get_option('url');
  if (filter_var($url, FILTER_VALIDATE_URL) === FALSE) {
    drush_set_error('SITE_AUDIT_GOOGLE_INSIGHTS_NO_URL', dt('A valid URL is required.'));
  }

  // Optional impact.
  $impact = drush_get_option('impact');
  if ($impact) {
    if ($impact < 0 || !is_numeric($impact)) {
      drush_set_error('SITE_AUDIT_GOOGLE_INSIGHTS_BAD_IMPACT_FILTER', dt('Impact filter must be a number greater than 0.'));
    }
  }

  // Optional limit.
  $limit = drush_get_option('limit');
  if ($limit) {
    if ($limit < 0 || !is_numeric($limit) || ($limit != (int) $limit)) {
      drush_set_error('SITE_AUDIT_GOOGLE_INSIGHTS_BAD_LIMIT', dt('Limit must be an integer greater than 0.'));
    }
  }
}

/**
 * Audit security validation.
 */
function drush_site_audit_audit_security_validate() {
  return site_audit_version_check();
}

/**
 * Audit status validation.
 */
function drush_site_audit_audit_status_validate() {
  return drush_site_audit_generic_callback_validate();
}

/**
 * Audit users validation.
 */
function drush_site_audit_audit_users_validate() {
  return drush_site_audit_generic_callback_validate();
}

/**
 * Audit Views validation.
 */
function drush_site_audit_audit_views_validate() {
  return drush_site_audit_generic_callback_validate();
}

/**
 * Audit watchdog validation.
 */
function drush_site_audit_audit_watchdog_validate() {
  return drush_site_audit_generic_callback_validate();
}
