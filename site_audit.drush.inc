<?php
/**
 * @file
 * Drupal site auditing commands.
 */

// Autoload Report classes.
spl_autoload_register(function ($class) {
  if (strpos($class, 'SiteAuditReport') === 0) {
    require_once 'Report/' . substr($class, strlen('SiteAuditReport')) . '.php';
  }
});

/**
 * Implements hook_drush_command().
 */
function site_audit_drush_command() {
  $items = array();

  $options = array(
    'html' => array(
      'description' => dt('If set, render as HTML report.'),
    ),
    'detail' => array(
      'description' => dt('If set, provided detailed responses.'),
    ),
  );

  $vendor_options = array(
    'vendor' => array(
      'description' => dt('If used, will adjust recommendations based on a Vendor\'s specific needs. Currently supported: pantheon'),
      'example-value' => dt('pantheon'),
      'value' => 'required',
    ),
  );

  $options_all = array_merge($options, $vendor_options);
  $arguments_all = array();

  $items['audit_cache'] = array(
    'description' => dt('Audit Drupal\'s caching settings.'),
    'aliases' => array('ac'),
    'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_FULL,
    'options' => array_merge($options, $vendor_options),
  );

  $options_db = array(
    'min_rows' => array(
      'description' => dt('Minimum number of rows to check for. Defaults to 1000.'),
      'example-value' => dt('1000'),
      'value' => 'required',
    ),
    'expected_collation' => array(
      'description' => dt('Check that all tables are of an expected collation. Defaults to utf8_general_ci.'),
      'example-value' => dt('utf8_general_ci'),
      'value' => 'required',
    ),
  );
  $options_all = array_merge($options, $options_db);
  $items['audit_database'] = array(
    'description' => dt('Report information about a site environment\'s database.'),
    'aliases' => array('ad'),
    'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_FULL,
    'options' => array_merge($options, $options_db),
  );

  $items['audit_extensions'] = array(
    'description' => dt('Audit extensions (modules and themes).'),
    'aliases' => array('ae'),
    'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_FULL,
    'options' => array_merge($options, $vendor_options),
  );

  $items['audit_codebase'] = array(
    'description' => dt('Audit the codebase.'),
    'aliases' => array('acb'),
    'bootstrap' => DRUSH_BOOTSTRAP_DRUSH,
    'options' => $options,
  );

  $items['audit_cron'] = array(
    'description' => dt('Audit cron.'),
    'aliases' => array('acr'),
    'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_FULL,
    'options' => $options,
  );

  $items['audit_watchdog'] = array(
    'description' => dt('Audit the database logs.'),
    'aliases' => array('aw'),
    'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_FULL,
    'options' => $options,
  );

  $items['audit_best_practices'] = array(
    'description' => dt('Audit best practices used.'),
    'aliases' => array('abp'),
    'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_FULL,
    'options' => array_merge($options, $vendor_options),
  );

  $items['audit_views'] = array(
    'description' => dt('Audit Views.'),
    'aliases' => array('av'),
    'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_FULL,
    'options' => $options,
  );

  $arguments_gi = array(
    'key' => dt('Google Code API Key - see https://developers.google.com/speed/docs/insights/v1/getting_started#auth'),
    'url' => dt('The URL that you wish to test.'),
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
  $items['audit_google_insights'] = array(
    'description' => dt('Analyze a site using Google PageSpeed Insights.'),
    'aliases' => array('agi'),
    // Minimal bootstrap.
    'bootstrap' => DRUSH_BOOTSTRAP_DRUSH,
    'arguments' => $arguments_gi,
    'options' => array_merge($options, $options_gi),
  );

  $items['audit_all'] = array(
    'description' => dt('Executes every Site Audit Report'),
    'aliases' => array('aa'),
    'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_FULL,
    'options' => array_merge($options_all, array(
      'skip' => array(
        'description' => dt('Name(s) of reports to skip, comma separated.'),
        'example-value' => dt('insights'),
        'value' => 'required',
      ),
    )),
    'arguments' => $arguments_all,
  );

  return $items;
}

/**
 * Implements hook_drush_help().
 */
function site_audit_drush_help($section) {
  switch ($section) {
    case 'drush:audit_all':
      return dt('Execute every Site Audit report');

    case 'drush:audit_cache':
      return dt('Audit Drupal\'s caching settings');

    case 'drush:audit_codebase':
      return dt('Audit the codebase of a Drupal site');

    case 'drush:audit_database':
      return dt('Audit the database structure and contents');

    case 'drush:audit_extensions':
      return dt('Audit modules and themes (extensions)');

    case 'drush:audit_watchdog':
      return dt('Audit Drupal\'s database logs');

    case 'drush:audit_best_practices':
      return dt('Audit site for best practices');

    case 'drush:audit_views':
      return dt('Audit Views displays');

    case 'meta:site_audit:title':
      return dt('Tools for auditing a Drupal site');

  }
}

/**
 * Execute every single report.
 */
function drush_site_audit_audit_all($url = '', $key = '') {
  $reports_to_skip = array();
  if (drush_get_option('skip')) {
    $reports_to_skip = explode(',', drush_get_option('skip'));
  }
  if ($handle = opendir(__DIR__ . '/Report')) {
    $reports_to_render = array();
    while (FALSE !== ($entry = readdir($handle))) {
      if (!in_array($entry, array('.', '..', 'Abstract.php'))) {
        $file_name = explode('.', $entry);
        $report_name = $file_name[0];
        $report_class = 'SiteAuditReport' . $report_name;
        // Allow a specific report to be skipped.
        if (!in_array(strtolower($report_name), $reports_to_skip)) {
          // Special case for arguments.
          if ($report_name == 'Insights') {
            $report = new $report_class($key, $url);
          }
          else {
            $report = new $report_class();
          }
          $reports_to_render[get_class($report)] = $report;
        }
      }
    }
    closedir($handle);

    if (empty($reports_to_render)) {
      drush_set_error('SITE_AUDIT_NO_REPORTS', dt('No reports are available!'));
      return;
    }

    // Header.
    $report_time = dt('Generated at @time', array('@time' => date('r')));
    if (drush_get_option('html')) {
      $report_title = '<a href="https://drupal.org/project/site_audit">' . dt('Site Audit') . '</a>';
      $report_title .= ' ' . dt('Report for') . ' ' . l($url, $url);
      echo '<h1>' . $report_title . '</h1>';
      echo '<p><em>' . $report_time . '</em></p>';
    }
    else {
      drush_print(dt('Site Audit Report for @url', array('@url' => $url)));
      drush_print($report_time);
      drush_print();
    }

    // Table of Contents; HTML only.
    if (drush_get_option('html')) {
      echo '<h2 id="top">' . dt('Table of contents') . '</h2><ul>';
      foreach ($reports_to_render as $report) {
        echo '<li>' . l($report->getLabel(), '', array(
          'fragment' => get_class($report),
          'external' => TRUE,
        ));
        if ($report->getPercent() != SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO) {
          echo ' (' . $report->getPercent() . '%)</li>';
        }
        echo '</li>';
      }
      echo '</ul>';
    }
    // Render reports.
    echo '<h2>' . dt('Reports') . '</h2>';
    foreach ($reports_to_render as $report) {
      $report->render();
      if (drush_get_option('html')) {
        echo '<p><em>' . l(dt('Back to top'), '', array(
          'fragment' => 'top',
          'external' => TRUE,
        )) . '</em></p>';
      }
    }
  }
}

/**
 * Validate parameters.
 *
 * @param string $url
 *   URL to check.
 * @param string $key
 *   Google API key.
 */
function drush_site_audit_audit_all_validate($url = '', $key = '') {
  if (filter_var($url, FILTER_VALIDATE_URL) === FALSE) {
    drush_set_error('SITE_AUDIT_ALL_NO_URL', dt('A valid URL is required.'));
  }
}

/**
 * Check Drupal's caching settings and make recommendations as necessary.
 */
function drush_site_audit_audit_cache() {
  $report = new SiteAuditReportCache();
  $report->render();
}

/**
 * Audit the database associated with a Drupal site.
 */
function drush_site_audit_audit_database() {
  $report = new SiteAuditReportDatabase();
  $report->render();
}

/**
 * Audit modules and themes (extensions) for a given Drupal site.
 */
function drush_site_audit_audit_extensions() {
  $report = new SiteAuditReportExtensions();
  $report->render();
}

/**
 * Audit the executable codebase of a Drupal site.
 */
function drush_site_audit_audit_codebase() {
  $report = new SiteAuditReportCodebase();
  $report->render();
}

/**
 * Audit the database logs of a Drupal site.
 */
function drush_site_audit_audit_watchdog() {
  $report = new SiteAuditReportWatchdog();
  $report->render();
}

/**
 * Audit the database logs of a Drupal site.
 */
function drush_site_audit_audit_best_practices() {
  $report = new SiteAuditReportBestPractices();
  $report->render();
}

/**
 * Audit Views.
 */
function drush_site_audit_audit_views() {
  $report = new SiteAuditReportViews();
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
function drush_site_audit_audit_google_insights_validate($url = '', $key = '') {
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
 * @param string $key
 *   Google API key.
 * @param string $url
 *   URL to check.
 */
function drush_site_audit_audit_google_insights($url, $key) {
  $report = new SiteAuditReportInsights($url, $key);
  $report->render();
}

/**
 * Render a Cron report.
 */
function drush_site_audit_audit_cron() {
  $report = new SiteAuditReportCron();
  $report->render();
}
