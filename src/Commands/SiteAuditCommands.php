<?php

namespace Drupal\site_audit\Commands;

use Drupal\site_audit\Renderer\Html;
use Drupal\site_audit\Renderer\Markdown;
use Drupal\site_audit\Renderer\Json;
use Drupal\site_audit\Renderer\Console;
use Consolidation\OutputFormatters\StructuredData\RowsOfFields;
use Drush\Commands\DrushCommands;
use Drush\Drush;
use Psr\Log\LoggerAwareInterface;
use Robo\Contract\ConfigAwareInterface;
use Robo\Contract\IOAwareInterface;
use Robo\Common\IO;
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
   * Run Site Audit report.
   *
   * @param $report
   *   The particular report to run. Omit this argument to choose from available reports.
   *
   * @option skip
   *   List of available reports.
   * @option format
   *   Format you which the report is to be in (html, text, json, markdown)
   * @option detail
   *   Show details when no issues found for the check.
   * @option bootstrap
   *   Wrap the report in HTML with Bootstrap derived styles.
   * @usage site_audit:audit
   *   Run all Site Audit reports
   *
   * @command site_audit:audit
   * @aliases audit
   * @usage
   *   audit watchdog
   * @usage
   *   audit --skip=block,status
   */
  public function audit($report, $options = ['skip' => 'none', 'format' => 'text', 'detail' => FALSE, 'bootstrap' => FALSE]) {
    $boot_manager = Drush::bootstrapManager();

    $output = $this->getOutput();
    $reportManager = \Drupal::service('plugin.manager.site_audit_report');
    $reportDefinitions = $reportManager->getDefinitions();

    $reports = [];
    if ($report == 'all') {
      // Run all reports unless it is explicitly skipped.
      $skipped = explode(',', $options['skip']);
      foreach ($reportDefinitions as $report) {
        $isSkipped = array_search($report['id'], $skipped);
        if ($isSkipped === FALSE) {
          $reports[] = $reportManager->createInstance($report['id'], $options);
        }
      }
    }
    elseif (!empty($report)) {
      $reports[] = $reportManager->createInstance($report, $options);
    }

    switch ($options['format']) {
      case 'html':
        $renderer = new Html($reports, $this->logger, $options, $output);
        $out .= $renderer->render(TRUE);
        break;

      case 'json';
        foreach ($reports as $report) {
          $renderer = new Json($report, $this->logger, $options, $output);
          $out .= $renderer->render(TRUE);
        }
        break;

      case 'markdown':
        foreach ($reports as $report) {
          $renderer = new Markdown($report, $this->logger, $options, $output);
          $out .= $renderer->render(TRUE);
        }
        break;

      case 'text':
      default:
        foreach ($reports as $report) {
          $renderer = new Console($report, $this->logger, $options, $output);
          $out .= $renderer->render(TRUE);
        }
        break;
    }

    return $out;
  }

  /**
   * Take Drupal\Core\StringTranslation\TranslatableMarkup and return the string.
   */
  public function interpolate($message, array $context = []) {
    if (get_class($message) == 'Drupal\Core\StringTranslation\TranslatableMarkup') {
      return $message->render();
    }
    return $message;
  }

  /**
   * @hook interact site_audit:audit
   */
  public function interactSiteAudit($input, $output) {
    $boot_manager = Drush::bootstrapManager();
    if (empty($input->getArgument('report'))) {
      $reports = $this->getReports($boot_manager->hasBootstrapped(DRUSH_BOOTSTRAP_DRUPAL_FULL));
      $choices = [
        'all' => $this->interpolate($this->t('All')),
      ];
      foreach ($reports as $report) {
        $choices[$report['id']] = $this->interpolate($report['name']);
      }
      $choice = $this->io()->choice(dt("Choose a report to run"), $choices, 'all');
      $input->setArgument('report', $choice);
    }
  }

  /**
   *
   */
  public function getReports($include_bootstrapped_types = FALSE) {
    $reportManager = \Drupal::service('plugin.manager.site_audit_report');
    $reportDefinitions = $reportManager->getDefinitions();
    return $reportDefinitions;
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
    foreach ($reportDefinitions as $report) {
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
