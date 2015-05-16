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

  $items['audit-best-practices'] = array(
    'description' => dt('Audit best practices used.'),
    'aliases' => array('abp'),
    'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_FULL,
    'options' => array_merge(site_audit_common_options(), $vendor_options),
    'checks' => array(
      'Fast404',
    ),
  );

  $items['audit-cache'] = array(
    'description' => dt('Audit Drupal\'s caching settings.'),
    'aliases' => array('ac'),
    'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_FULL,
    'options' => array_merge(site_audit_common_options(), $vendor_options),
    'checks' => array(
      'PageInternal',
      'AllBackends',
      'DefaultBackends',
      'UsedBackends',
      'PageExpire',
      'PreprocessCss',
      'PreprocessJs',
    ),
  );

  $items['audit-extensions'] = array(
    'description' => dt('Audit extensions (modules and themes).'),
    'aliases' => array('ae'),
    'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_FULL,
    'options' => array_merge(site_audit_common_options(), $vendor_options),
    'checks' => array(
      'Count',
      'Unrecommended',
    ),
  );

  $items['audit-all'] = array(
    'description' => dt('Executes every Site Audit Report'),
    'aliases' => array('aa'),
    'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_FULL,
    'arguments' => $arguments_all,
    'reports' => array(
      'BestPractices',
      'Cache',
      'Extensions',
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
 * Validate execution of all reports.
 */
function drush_site_audit_audit_all_validate($url = '', $key = '') {
  return site_audit_version_check();
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
