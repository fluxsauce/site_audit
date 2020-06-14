<?php

namespace Drupal\site_audit\Controller;

use Drupal\Core\Controller\ControllerBase;

use Drupal\site_audit\Renderer\Html;

/**
 * Class SiteAuditController.
 *
 * @package Drupal\site_audit\Controller
 */
class SiteAuditController extends ControllerBase {

  /**
   * Audit.
   *
   * @return string
   *   Rendered report output.
   */
  public function audit() {
    $reportManager = \Drupal::service('plugin.manager.site_audit_report');
    $reportDefinitions = $reportManager->getDefinitions();
    $saved_reports = \Drupal::config('site_audit.settings')->get('reports');
    $reports = [];
    // Check to see if there is anything checked
    // the array is empty, so the settings form hasn't been submitted.
    if (!empty($saved_reports) &&
    // They are not all unchecked.
      count(array_flip($saved_reports)) > 1) {
      foreach ($saved_reports as $saved_report) {
        if ($saved_report) {
          $reports[] = $reportManager->createInstance($saved_report);
        }
      }
    }
    // There are no reports selected, so run them all.
    else {
      foreach ($reportDefinitions as $reportDefinition) {
        $reports[] = $reportManager->createInstance($reportDefinition['id']);
      }
    }

    $out = '';

    $renderer = new Html($reports, NULL, ['detail' => TRUE, 'inline' => TRUE, 'uri' => \Drupal::request()->getHost()]);
    return $renderer->render(TRUE);
  }

}
