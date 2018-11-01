<?php

namespace Drupal\site_audit\Commands;

use Drupal\site_audit\Renderer\Html;
use Drupal\site_audit\Renderer\Markdown;
use Drupal\site_audit\Renderer\Json;
use Drupal\site_audit\Renderer\Console;
use Consolidation\OutputFormatters\StructuredData\RowsOfFields;
use Drush\Commands\DrushCommands;
use Drush\Drush;
use Drush\Style\DrushStyle;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Robo\Common\ConfigAwareTrait;
use Robo\Contract\ConfigAwareInterface;
use Robo\Contract\IOAwareInterface;
use Robo\Common\IO;
use Symfony\Component\Console\Input\InputOption;

/**
 * SiteAudit Drush commandfile.
 */
class SiteAuditCommands extends DrushCommands implements IOAwareInterface, LoggerAwareInterface, ConfigAwareInterface {

  /**
   * Run Site Audit report
   *
   * @option reports
   *   List of reports to include, comma separated.
   * @option skip
   *   List of available reports.
   * @option format
   *   Format you wich the report to be in (html, text, json, markdown)
   * @usage site_audit:audit
   *   Run all Site Audit reports
   *
   * @command site_audit:audit
   * @aliases audit
   * @usage
   *   audit --reports=watchdog,extensions
   * @usage
   *   audit --skip=block,status
   */
  public function audit($options = ['skip' => 'none', 'reports' => 'all', 'format' => 'text']) {
    $output = $this->getOutput();
    $reportManager = \Drupal::service('plugin.manager.site_audit_report');
    $reportDefinitions = $reportManager->getDefinitions();

    $reports = [];
    if (!empty($options['reports']) && $options['reports'] != 'all') {
      // run the reports requested
      foreach(explode(',', $options['reports']) AS $report_id) {
        $report_id = trim($report_id);
        if (isset($reportDefinitions[$report_id])) {
          $reports[] = $reportManager->createInstance($report_id);
        }
      }
    }
    else {
      // run all reports unless it is explicitly skipped
      $skipped = explode(',', $options['skip']);
      foreach ($reportDefinitions AS $report) {
        $isSkipped = array_search($report['id'], $skipped);
        if ($isSkipped === FALSE) {
          $reports[] = $reportManager->createInstance($report['id'], $options);
        }
      }
    }

    foreach ($reports AS $report) {
      switch ($options['format']) {
        case 'html':
          $renderer = new Html($reports, $output);
          $out .= $renderer->render(TRUE);
          break;
        case 'json';
          $renderer = new Json($reports, $output);
          $out .= $renderer->render(TRUE);
          break;
        case 'markdown':
          $renderer = new Markdown($report, $output);
          $out .= $renderer->render(TRUE);
          break;
        case 'text':
        default:
          $renderer = new Console($report, $this->logger, $output);
          $out .= $renderer->render(TRUE);
          break;
      }
    }

    //print_r($options);
    return $out;
  }

  /**
   * Generate a list of all available reports.
   *
   * @field-labels
   *   report_id: Report ID
   *   report_name: Report Name
   *   report_description : Report Description
   *   check_id: Check ID
   *   check_name: Check Name
   *   check_description: Check Description
   * @default-fields report_id,report_name,check_name,check_description
   *
   * @command site_audit:list
   * @aliases audit-list
   *
   * @return \Consolidation\OutputFormatters\StructuredData\RowsOfFields
   */
  public function list() {
    $reportManager = \Drupal::service('plugin.manager.site_audit_report');
    $reportDefinitions = $reportManager->getDefinitions();
    $checkManager = \Drupal::service('plugin.manager.site_audit_check');
    $checkDefinitions = $checkManager->getDefinitions();
    $rows = [];
    $report_id = '';
    foreach ($reportDefinitions AS $report) {
      if ($report_id != $report['id'] && !empty($report_id)) {
        $rows[] = [];
      }
      $thisReport = $reportManager->createInstance($report['id']);
      $checks = $thisReport->getChecksList();
      foreach ($checks as $check) {
        $rows[] = [
          'report_id' => $report_id == $report['id'] ? '' : $report['id'],
          'report_name' => $report_id == $report['id'] ? '' : $report['name'],
          'report_description' => $report_id == $report['id'] ? '' : $report['description'],
          'check_id' => $checkDefinitions[$check]['id'],
          'check_name' => $checkDefinitions[$check]['name'],
          'check_description' => $checkDefinitions[$check]['description'],
        ];
        $report_id = $report['id'];
      }
    }
    return new RowsOfFields($rows);
  }
}
