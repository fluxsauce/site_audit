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
  if (substr($class, 0, 10) == 'AuditCheck') {
    require_once 'checks/class.' . $class . '.php';
  }
  elseif (substr($class, 0, 11) == 'AuditReport') {
    require_once 'reports/class.' . $class . '.php';
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

  return $items;
}

/**
 * Implements hook_drush_help().
 */
function site_audit_drush_help($section) {
  switch ($section) {
    case 'drush:audit_cache':{
      return dt('Audit Drupal\'s caching settings.');
      break;
    }
    case 'drush:audit_codebase':{
      return dt('Audit the codebase of a Drupal site.');
      break;
    }
    case 'drush:audit_database':{
      return dt('Audit the database structure and contents.');
      break;
    }
    case 'drush:audit_extensions':{
      return dt('Audit modules and themes (extensions).');
      break;
    }
    case 'drush:audit_watchdog':{
      return dt('Audit Drupal\'s database logs.');
      break;
    }
    case 'drush:audit_best_practices':{
      return dt('Audit site for best practices.');
      break;
    }
    case 'drush:audit_views':{
      return dt('Audit Views displays.');
      break;
    }
    case 'meta:site_audit:title':{
      return dt('Tools for auditing a Drupal site, providing actionable suggestions when possible.');
      break;
    }
  }
}

/**
 * Check Drupal's caching settings and make recommendations as necessary.
 */
function drush_site_audit_audit_cache() {
  $check = new AuditReportCache();
  if (drush_get_option('html')) {
    echo $check->toHtml();
  }
  else {
    $check->toDrush();
  }
}

/**
 * Audit the database associated with a Drupal site.
 */
function drush_site_audit_audit_database() {
  $check = new AuditReportDatabase();
  if (drush_get_option('html')) {
    echo $check->toHtml();
  }
  else {
    $check->toDrush();
  }
}

/**
 * Audit modules and themes (extensions) for a given Drupal site.
 */
function drush_site_audit_audit_extensions() {
  $check = new AuditReportExtensions();
  if (drush_get_option('html')) {
    echo $check->toHtml();
  }
  else {
    $check->toDrush();
  }
}

/**
 * Audit the executable codebase of a Drupal site.
 */
function drush_site_audit_audit_codebase() {
  $check = new AuditReportCodebase();
  if (drush_get_option('html')) {
    echo $check->toHtml();
  }
  else {
    $check->toDrush();
  }
}

/**
 * Audit the database logs of a Drupal site.
 */
function drush_site_audit_audit_watchdog() {
  $check = new AuditReportWatchdog();
  if (drush_get_option('html')) {
    echo $check->toHtml();
  }
  else {
    $check->toDrush();
  }
}

/**
 * Audit the database logs of a Drupal site.
 */
function drush_site_audit_audit_best_practices() {
  $check = new AuditReportBestPractices();
  if (drush_get_option('html')) {
    echo $check->toHtml();
  }
  else {
    $check->toDrush();
  }
}

/**
 * Audit Views.
 */
function drush_site_audit_audit_views() {
  $check = new AuditReportViews();
  if (drush_get_option('html')) {
    echo $check->toHtml();
  }
  else {
    $check->toDrush();
  }
}


/**
 * Make a Views caching setting readable.
 * @param array $cache_array
 * @return string
 */
function pantheon_views_cache_to_human($cache_array) {
  if (!is_array($cache_array)) {
    return (string) $cache_array;
  }
  $cache_text = array();
  foreach ($cache_array as $key => $value) {
    $cache_text[] = "$key: $value";
  }
  return implode(', ', $cache_text);
}
