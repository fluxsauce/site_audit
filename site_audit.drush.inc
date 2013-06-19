<?php
/**
 * @file
 * Drupal site auditing commands.
 */
spl_autoload_register('site_audit_autoloader');

function site_audit_autoloader($class) {
  if (substr($class, 0, 10) == 'AuditCheck') {
    require_once 'checks/class.' . $class . '.php';
  }
  else if (substr($class, 0, 11) == 'AuditReport') {
    require_once 'reports/class.' . $class . '.php';
  }
}

/**
 * Implements hook_drush_command().
 */
function site_audit_drush_command() {
  $items = array();

  $items['audit_cache'] = array(
    'description' => dt('Audit Drupal\'s caching settings.'),
    'aliases' => array('ac'),
    'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_FULL,
    'options' => array(
      'html' => array(
        'description' => dt('If set, render as HTML report.'),
      ),
      'vendor' => array(
        'description' => dt('If used, will adjust recommendations based on a Vendor\'s specific needs. Currently supported: pantheon'),
        'example-value' => dt('pantheon'),
        'value' => 'required',
      ),
    ),
  );

  return $items;
}

/**
 * Implements hook_drush_help().
 */
function site_audit_drush_help($section) {
  switch ($section) {
    case 'drush:audit_cache':{
      return dt('Audit Drupal\'s caching settings and provide actionable suggestions if needed.');
      break;
    }
    case 'meta:site_audit:title':{
      return dt('Tools for auditing a Drupal site');
      break;
    }
  }
}

/**
 * Check Drupal's caching settings and make recommendations as necessary.
 */
function drush_site_audit_audit_cache() {
  $cache = new AuditReportCache();
  if (drush_get_option('html')) {
    echo $cache->toHtml();
  }
  else {
    $cache->toDrush();
  }
}