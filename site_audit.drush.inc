<?php
/**
 * @file
 * Drupal site auditing commands.
 */

spl_autoload_register('site_audit_autoloader');

/**
 * Define site_audit autoloader.
 */
function site_audit_autoloader($class) {
  if (strpos($class, 'SiteAuditCheck') === 0) {
    require_once 'Check/' . substr($class, strlen('SiteAuditCheck')) . '.php';
  }
  elseif (strpos($class, 'SiteAuditReport') === 0) {
    require_once 'Report/' . substr($class, strlen('SiteAuditReport')) . '.php';
  }
}

/**
 * Implements hook_drush_command().
 */
function site_audit_drush_command() {
  $items = array();

  $options = array(
    'html' => array(
      'description' => dt('If set, render as HTML report.'),
    ),
  );

  $vendor_options = array(
    'vendor' => array(
      'description' => dt('If used, will adjust recommendations based on a Vendor\'s specific needs. Currently supported: pantheon'),
      'example-value' => dt('pantheon'),
      'value' => 'required',
    ),
  );

  $items['audit_cache'] = array(
    'description' => dt('Audit Drupal\'s caching settings.'),
    'aliases' => array('ac'),
    'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_FULL,
    'options' => array_merge($options, $vendor_options),
  );

  $items['audit_database'] = array(
    'description' => dt('Report information about a site environment\'s database.'),
    'aliases' => array('ad'),
    'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_FULL,
    'options' => array_merge($options, array(
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
    )),
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
    'options' => array_merge($options, $vendor_options),
  );

  $items['audit_watchdog'] = array(
    'description' => dt('Audit the database logs.'),
    'aliases' => array('aw'),
    'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_FULL,
    'options' => array_merge($options, $vendor_options),
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
    'options' => array_merge($options, $vendor_options),
  );

  $items['audit_database'] = array(
    'description' => dt('Report information about a site environment\'s database.'),
    'aliases' => array('ad'),
    'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_FULL,
    'options' => array_merge($options, array(
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
    )),
  );

  $items['audit_google_insights'] = array(
    'description' => dt('Analyze a site using Google PageSpeed Insights.'),
    'aliases' => array('agi'),
    // Minimal bootstrap.
    'bootstrap' => DRUSH_BOOTSTRAP_DRUSH,
    'arguments' => array(
      'key' => dt('Google Code API Key - see https://developers.google.com/speed/docs/insights/v1/getting_started#auth'),
      'url' => dt('The URL that you wish to test.'),
    ),
    'options' => array_merge($options, array(
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
    )),
  );

  return $items;
}

/**
 * Implements hook_drush_help().
 */
function site_audit_drush_help($section) {
  switch ($section) {
    case 'drush:audit_cache':
      return dt('Audit Drupal\'s caching settings.');

    case 'drush:audit_codebase':
      return dt('Audit the codebase of a Drupal site.');

    case 'drush:audit_database':
      return dt('Audit the database structure and contents.');

    case 'drush:audit_extensions':
      return dt('Audit modules and themes (extensions).');

    case 'drush:audit_watchdog':
      return dt('Audit Drupal\'s database logs.');

    case 'drush:audit_best_practices':
      return dt('Audit site for best practices.');

    case 'drush:audit_views':
      return dt('Audit Views displays.');

    case 'meta:site_audit:title':
      return dt('Tools for auditing a Drupal site, providing actionable suggestions when possible.');

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
 * @param string $key
 *   Google API key.
 * @param string $url
 *   URL to check.
 */
function drush_site_audit_audit_google_insights_validate($key = '', $url = '') {
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
function drush_site_audit_audit_google_insights($key, $url) {
  $report = new SiteAuditReportInsights($key, $url);
  $report->render();
}
