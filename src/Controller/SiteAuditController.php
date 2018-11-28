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
    $saved_reports = \Drupal::config('site_audit.settings')->get('reports');
    $reports = [];
    // check to see if there is anything checked
    if (!empty($saved_reports) && // the array is empty, so the settings form hasn't been submitted
      count(array_flip($saved_reports)) > 1) { // they are not all unchecked
      foreach ($saved_reports as $saved_report) {
        if ($saved_report) {
          $reports[] = $reportManager->createInstance($saved_report);
        }
      }
    }
    else { // there are no reports selected, so run them all
      foreach ($reportDefinitions AS $reportDefinition) {
        $reports[] = $reportManager->createInstance($reportDefinition['id']);
      }
    }

    $out = '';

    $renderer = new Html($reports, NULL, ['detail' => TRUE, 'inline' => TRUE, 'uri' => \Drupal::request()->getHost()]);
    return $renderer->render(TRUE);
  }

  /**
   * @inherit
   */
  public function getDefinitions() {
    $definitions = parent::getDefinitions();
    dpm($definitions, '$definitions');
    return $definitions;
  }

}
