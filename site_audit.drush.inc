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
  );

  $vendor_options = array(
    'vendor' => array(
      'description' => dt("If used, will adjust recommendations based on a Vendor's specific needs. Currently supported: pantheon"),
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
  );
  $options_all = array_merge($options_all, $options_db);
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
    'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_FULL,
    'options' => $options,
  );

  $items['audit_content'] = array(
    'description' => dt('Audit content.'),
    'aliases' => array('acn'),
    'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_FULL,
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
    'options' => $options,
  );

  $items['audit_block'] = array(
    'description' => dt('Audit Block.'),
    'aliases' => array('ab'),
    'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_FULL,
    'options' => $options,
  );

  $items['audit_status'] = array(
    'description' => dt('Audit Status.'),
    'aliases' => array('as'),
    'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_FULL,
    'options' => $options,
  );

  $items['audit_users'] = array(
    'description' => dt('Audit Users.'),
    'aliases' => array('au'),
    'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_FULL,
    'options' => $options,
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
  $items['audit_google_insights'] = array(
    'description' => dt('Analyze a site using Google PageSpeed Insights.'),
    'aliases' => array('agi'),
    // Minimal bootstrap.
    'bootstrap' => DRUSH_BOOTSTRAP_DRUSH,
    'arguments' => $arguments_gi,
    'options' => array_merge($options, $options_gi),
  );

  $arguments_all['key'] = dt('URL for Site Audit Report, also used for Google PageSpeed Insights report.');

  $items['audit_all'] = array(
    'description' => dt('Executes every Site Audit Report'),
    'aliases' => array('aa'),
    'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_FULL,
    'options' => array_merge($options_all, array(
      'gist' => array(
        'description' => dt('Publish result in a private gist. Set value with a Personal API Access Token created from https://github.com/settings/applications'),
        'value' => 'required',
        'example-value' => 'YOURTOKEN',
      ),
      'bootstrap' => array(
        'description' => dt('If enabled, will wrap the generated report with Twitter Bootstrap 3.0 inline HTML.'),
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

    case 'drush:audit_content':
      return dt('Audit the content of a Drupal site');

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
            $report = new $report_class($url, $key);
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
      return drush_set_error('SITE_AUDIT_NO_REPORTS', dt('No reports are available!'));
    }

    if (drush_get_option('bootstrap')) {
      echo file_get_contents(__DIR__ . '/html/header.html');
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
      echo file_get_contents(__DIR__ . '/html/footer.html');
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
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
      'Content-Type: application/json',
      'Content-Length: ' . strlen($data_string),
      'Authorization: bearer ' . $gist,
    ));

    $result = curl_exec($ch);
    $json = json_decode($result);
    if (!isset($json->html_url)) {
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
 * @param string $url
 *   URL to check.
 * @param string $key
 *   Google API key.
 */
function drush_site_audit_audit_google_insights($url, $key) {
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
