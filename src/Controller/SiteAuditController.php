<?php

namespace Drupal\site_audit\Controller;

use Drupal\Core\Controller\ControllerBase;

use Drupal\site_audit\Renderer\Html;
use Drupal\site_audit\Reports\Cache;
use Drupal\site_audit\Reports\Extensions;

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
    $reports = [];

    foreach ($reportDefinitions AS $reportDefinition) {
      $reports[] = $reportManager->createInstance($reportDefinition['id']);
    }

    $out = '';

    foreach ($reports as $report) {
      $renderer = new Html($report);
      $out .= $renderer->render(TRUE);
    }/**/
    //$out = 'test output';

    return [
      '#type' => 'markup',
      '#markup' => $out,
    ];
  }

}
