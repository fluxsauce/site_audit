<?php
/**
 * @file
 * Drupal site auditing commands.
 */

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
  $arguments_all = array();

  $items['audit_best_practices'] = array(
    'description' => dt('Audit best practices used.'),
    'aliases' => array('abp'),
    'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_FULL,
    'options' => array_merge(site_audit_common_options(), $vendor_options),
    'checks' => array(
      'Settings',
      'SitesDefault',
      'SitesAll',
      'Multisite',
      'SitesSuperfluous',
      'Fast404',
      'PhpFilter',
    ),
  );

  $items['audit_block'] = array(
    'description' => dt('Audit Block.'),
    'aliases' => array('ab'),
    'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_FULL,
    'options' => site_audit_common_options(),
    'checks' => array(
      'Enabled',
      'Cache',
      'CacheReport',
    ),
  );

  $items['audit_cache'] = array(
    'description' => dt('Audit Drupal\'s caching settings.'),
    'aliases' => array('ac'),
    'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_FULL,
    'options' => array_merge(site_audit_common_options(), $vendor_options),
    'checks' => array(
      'Anon',
      'Lifetime',
      'PageExpire',
      'PageCompression',
      'PreprocessCss',
      'PreprocessJs',
      'Lock',
      'Backends',
      'DefaultClass',
      'Bins',
    ),
  );

  $items['audit_codebase'] = array(
    'description' => dt('Audit the codebase.'),
    'aliases' => array('acb'),
    'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_FULL,
    'options' => site_audit_common_options(),
    'checks' => array(
      'SizeFiles',
      'SizeAll',
      'ManagedFileCount',
      'ManagedFileSize',
    ),
  );

  $items['audit_content'] = array(
    'description' => dt('Audit content.'),
    'aliases' => array('acn'),
    'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_FULL,
    'options' => site_audit_common_options(),
    'checks' => array(
      'ContentTypes',
      'ContentTypesUnused',
      'DuplicateTitles',
      'Vocabularies',
      'VocabulariesUnused',
      'FieldEnabled',
      'FieldCount',
      'FieldInstances',
      'FieldsUnused',
    ),
  );

  $items['audit_cron'] = array(
    'description' => dt('Audit cron.'),
    'aliases' => array('acr'),
    'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_FULL,
    'options' => site_audit_common_options(),
    'checks' => array(
      'Enabled',
      'Running',
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
  $items['audit_database'] = array(
    'description' => dt('Report information about a site environment\'s database.'),
    'aliases' => array('ad'),
    'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_FULL,
    'options' => array_merge(site_audit_common_options(), $options_db),
    'checks' => array(
      'Size',
      'RowCount',
      'Collation',
      'Engine',
    ),
  );

  $items['audit_extensions'] = array(
    'description' => dt('Audit extensions (modules and themes).'),
    'aliases' => array('ae'),
    'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_FULL,
    'options' => array_merge(site_audit_common_options(), $vendor_options),
    'checks' => array(
      'Count',
      'Dev',
      'Unrecommended',
      'Version',
      'Duplicate',
      'Missing',
      'Disabled',
    ),
  );
  $items['audit_extensions']['options']['extension_count'] = array(
    'description' => dt('Specify threshold for average number of extensions'),
    'example-value' => dt('150'),
    'value' => 'required',
  );

  $arguments_gi = array(
    'url' => dt('The URL that you wish to test.'),
    'key' => dt('Google Code API Key - see https://developers.google.com/speed/docs/insights/v1/getting_started#auth'),
  );
  $arguments_all = array_merge($arguments_all, $arguments_gi);
  $options_gi = array(
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
  );
  $options_all = array_merge($options_all, $options_gi);
  $items['audit_insights'] = array(
    'description' => dt('Analyze a site using Google PageSpeed Insights.'),
    'aliases' => array('agi'),
    'bootstrap' => DRUSH_BOOTSTRAP_DRUSH,
    'arguments' => $arguments_gi,
    'options' => array_merge(site_audit_common_options(), $options_gi),
    'checks' => array(
      'Analyze',
    ),
  );

  $items['audit_security'] = array(
    'description' => dt('Audit the site for known security vulnerabilities.'),
    'aliases' => array('asec'),
    'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_FULL,
    'options' => site_audit_common_options(),
    'checks' => array(
      'MenuRouter',
    ),
  );

  $items['audit_status'] = array(
    'description' => dt('Audit Drupal\'s built-in status report.'),
    'aliases' => array('as'),
    'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_FULL,
    'options' => site_audit_common_options(),
    'checks' => array(
      'System',
    ),
  );

  $items['audit_users'] = array(
    'description' => dt('Audit Users.'),
    'aliases' => array('au'),
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

  $items['audit_views'] = array(
    'description' => dt('Audit Views.'),
    'aliases' => array('av'),
    'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_FULL,
    'options' => site_audit_common_options(),
    'checks' => array(
      'Enabled',
      'Count',
      'CacheResults',
      'CacheOutput',
    ),
  );

  $items['audit_watchdog'] = array(
    'description' => dt('Audit the database logs.'),
    'aliases' => array('aw'),
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

  $arguments_all['key'] = dt('URL for Site Audit Report, also used for Google PageSpeed Insights report.');

  $items['audit_all'] = array(
    'description' => dt('Executes every Site Audit Report'),
    'aliases' => array('aa'),
    'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_FULL,
    'arguments' => $arguments_all,
    'options' => array_merge($options_all, array(
      'gist' => array(
        'description' => dt('Publish result in a private gist. Set value with a Personal API Access Token created from https://github.com/settings/applications'),
        'value' => 'required',
        'example-value' => 'YOURTOKEN',
      ),
    )),
    'reports' => array(
      'BestPractices',
      'Block',
      'Cache',
      'Codebase',
      'Content',
      'Cron',
      'Database',
      'Extensions',
      'Insights',
      'Security',
      'Status',
      'Users',
      'Views',
      'Watchdog',
    ),
  );

  $items['site-audit-version'] = array(
    'description' => dt('Show the Site Audit version.'),
    'callback' => 'drush_site_audit_site_audit_version',
    'bootstrap' => DRUSH_BOOTSTRAP_DRUSH,
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
 * Validate execution of all reports.
 */
function drush_site_audit_audit_all_validate($url = '', $key = '') {
  return site_audit_version_check();
}

/**
 * Execute every single report.
 */
function drush_site_audit_audit_all($url = '', $key = '') {
  $gist = drush_get_option('gist');
  if ($gist) {
    ob_start();
  }

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
        if ($report_name == 'Insights') {
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
      $report_title = l(dt('Site Audit'), 'https://drupal.org/project/site_audit');
      if ($url) {
        $report_title .= ' ' . dt('report for !url', array(
          '!url' => l($url, $url),
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
        $buttons[] = l($button_text, '', array(
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
        echo '<div>' . l(dt('Back to top'), '', array(
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

  if ($gist) {
    $result = ob_get_contents();
    while (ob_get_level()) {
      ob_end_clean();
    }

    $file_name = 'site_audit.' . (drush_get_option('html') ? 'html' : 'txt');

    $data = array(
      'description' => $url . ' Site Audit',
      'public' => FALSE,
      'files' => array(
        $file_name => array(
          'content' => $result,
          'language' => (drush_get_option('html') ? 'html' : 'text'),
        ),
      ),
    );
    $data_string = json_encode($data);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.github.com/gists');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
      'Content-Type: application/json',
      'Authorization: token ' . $gist,
      'User-Agent: Drupal-Site-Audit',
    ));

    $result = curl_exec($ch);
    $json = json_decode($result);
    if (is_null($json)) {
      drush_set_error('SITE_AUDIT_GIST_NULL', dt('api.github.com did not provide JSON; raw result: @message', array(
        '@message' => $result,
      )));
    }
    elseif (!isset($json->html_url)) {
      drush_set_error('SITE_AUDIT_GIST_JSON', dt('api.github.com says: @message', array(
        '@message' => $json->message,
      )));
    }
    else {
      drush_log(dt('Result: @url', array('@url' => $json->html_url)), 'success');
    }
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
 * Check the Drupal major version.
 *
 * @return bool
 *   FALSE if unsupported.
 */
function site_audit_version_check() {
  $drupal_major_version = drush_drupal_major_version();
  if ($drupal_major_version < 7 || $drupal_major_version >= 8) {
    return drush_set_error('SITE_AUDIT_DRUPAL_7_ONLY', dt('Site Audit only supports Drupal 7.'));
  }
  return TRUE;
}

/**
 * Audit cache validation.
 */
function drush_site_audit_audit_cache_validate() {
  return site_audit_version_check();
}

/**
 * Check Drupal's caching settings and make recommendations as necessary.
 */
function drush_site_audit_audit_cache() {
  $report = new SiteAuditReportCache();
  $report->render();
}

/**
 * Audit content validation.
 */
function drush_site_audit_audit_content_validate() {
  return site_audit_version_check();
}

/**
 * Audit Drupal's content.
 */
function drush_site_audit_audit_content() {
  $report = new SiteAuditReportContent();
  $report->render();
}

/**
 * Audit database validation.
 */
function drush_site_audit_audit_database_validate() {
  return site_audit_version_check();
}

/**
 * Audit the database associated with a Drupal site.
 */
function drush_site_audit_audit_database() {
  $report = new SiteAuditReportDatabase();
  $report->render();
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
  return site_audit_version_check();
}

/**
 * Audit modules and themes (extensions) for a given Drupal site.
 */
function drush_site_audit_audit_extensions() {
  $report = new SiteAuditReportExtensions();
  $report->render();
}

/**
 * Audit codebase validation.
 */
function drush_site_audit_audit_codebase_validate() {
  return site_audit_version_check();
}

/**
 * Audit the executable codebase of a Drupal site.
 */
function drush_site_audit_audit_codebase() {
  $report = new SiteAuditReportCodebase();
  $report->render();
}

/**
 * Audit watchdog validation.
 */
function drush_site_audit_audit_watchdog_validate() {
  return site_audit_version_check();
}

/**
 * Audit the database logs of a Drupal site.
 */
function drush_site_audit_audit_watchdog() {
  $report = new SiteAuditReportWatchdog();
  $report->render();
}

/**
 * Audit best practices validation.
 */
function drush_site_audit_audit_best_practices_validate() {
  return site_audit_version_check();
}

/**
 * Audit Drupal best practices.
 */
function drush_site_audit_audit_best_practices() {
  $report = new SiteAuditReportBestPractices();
  $report->render();
}

/**
 * Audit Views validation.
 */
function drush_site_audit_audit_views_validate() {
  return site_audit_version_check();
}

/**
 * Audit Views.
 */
function drush_site_audit_audit_views() {
  $report = new SiteAuditReportViews();
  $report->render();
}

/**
 * Audit Block validation.
 */
function drush_site_audit_audit_block_validate() {
  return site_audit_version_check();
}

/**
 * Audit Block.
 */
function drush_site_audit_audit_block() {
  $report = new SiteAuditReportBlock();
  $report->render();
}

/**
 * Validate parameters.
 *
 * @param string $url
 *   URL to check.
 * @param string $key
 *   Google API key.
 */
function drush_site_audit_audit_insights_validate($url = '', $key = '') {
  // Just check the API key exists.
  if (!$key) {
    drush_set_error('SITE_AUDIT_GOOGLE_INSIGHTS_NO_KEY', dt('An API key is required; see https://developers.google.com/speed/docs/insights/v1/getting_started#auth'));
  }
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
 * Render a Google PageSpeed Insight report.
 *
 * @param string $url
 *   URL to check.
 * @param string $key
 *   Google API key.
 */
function drush_site_audit_audit_insights($url, $key) {
  $report = new SiteAuditReportInsights($url, $key);
  $report->render();
}

/**
 * Audit cron validation.
 */
function drush_site_audit_audit_cron_validate() {
  return site_audit_version_check();
}

/**
 * Render a Cron report.
 */
function drush_site_audit_audit_cron() {
  $report = new SiteAuditReportCron();
  $report->render();
}

/**
 * Audit users validation.
 */
function drush_site_audit_audit_users_validate() {
  return site_audit_version_check();
}

/**
 * Render a Users report.
 */
function drush_site_audit_audit_users() {
  $report = new SiteAuditReportUsers();
  $report->render();
}

/**
 * Audit status validation.
 */
function drush_site_audit_audit_status_validate() {
  return site_audit_version_check();
}

/**
 * Render a Status report.
 */
function drush_site_audit_audit_status() {
  $report = new SiteAuditReportStatus();
  $report->render();
}

/**
 * Audit security validation.
 */
function drush_site_audit_audit_security_validate() {
  return site_audit_version_check();
}

/**
 * Render a Security report.
 */
function drush_site_audit_audit_security() {
  $report = new SiteAuditReportSecurity();
  $report->render();
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
