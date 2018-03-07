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
    $reports = [
      new Cache(),
      new Extensions(),
    ];

    $out = '';

    foreach ($reports as $report) {
      $renderer = new Html($report);
      $out .= $renderer->render(TRUE);
    }

    return [
      '#type' => 'markup',
      '#markup' => $out,
    ];
  }

}
