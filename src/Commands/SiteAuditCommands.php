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
use Consolidation\AnnotatedCommand\Events\CustomEventAwareInterface;
use Consolidation\AnnotatedCommand\Events\CustomEventAwareTrait;
use Drush\Boot\AutoloaderAwareInterface;
use Drush\Boot\AutoloaderAwareTrait;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * SiteAudit Drush commandfile.
 */
class SiteAuditCommands extends DrushCommands implements IOAwareInterface, LoggerAwareInterface, ConfigAwareInterface, CustomEventAwareInterface, AutoloaderAwareInterface {

  use CustomEventAwareTrait;
  use AutoloaderAwareTrait;
  use StringTranslationTrait;

  /**
   * Run Site Audit report
   *
   * @param string $report The particular report to run. Omit this argument to choose from available reports.
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
  public function audit($report, $options = ['skip' => 'none', 'reports' => 'all', 'format' => 'text']) {
    $boot_manager = Drush::bootstrapManager();

    $output = $this->getOutput();
    $reportManager = \Drupal::service('plugin.manager.site_audit_report');
    $reportDefinitions = $reportManager->getDefinitions();

    $reports = [];
    if ($report == 'all') {
      // run all reports unless it is explicitly skipped
      $skipped = explode(',', $options['skip']);
      foreach ($reportDefinitions AS $report) {
        $isSkipped = array_search($report['id'], $skipped);
        if ($isSkipped === FALSE) {
          $reports[] = $reportManager->createInstance($report['id'], $options);
        }
      }
    }
    else if (!empty($report)) {
      $reports[] = $reportManager->createInstance($report, $options);
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
   * @hook interact site_audit:audit
   */
  public function interactSiteAudit($input, $output) {
    $boot_manager = Drush::bootstrapManager();
    if (empty($input->getArgument('report'))) {
      $reports = $this->getReports($boot_manager->hasBootstrapped(DRUSH_BOOTSTRAP_DRUPAL_FULL));
      $choices = [
        'all' => $this->t('All'),
      ];
      foreach ($reports AS $report) {
        $choices[$report['id']] = $report['name'];
      }
      $choice = $this->io()->choice(dt("Choose a report to run"), $choices, 'all');
      $input->setArgument('report', $choice);
    }
  }

  public function getReports($include_bootstrapped_types = false) {
    $reportManager = \Drupal::service('plugin.manager.site_audit_report');
    $reportDefinitions = $reportManager->getDefinitions();
    return $reportDefinitions;
    print('$reportDefinitions => ' . print_r($reportDefinitions, TRUE));
    //foreach ($reportDefinitions) AS $reports
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
