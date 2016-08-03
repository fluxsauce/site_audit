<?php

namespace Drupal\site_audit\Controller;

use Drupal\Core\Controller\ControllerBase;

use Drupal\site_audit\Renderer\Html;
use Drupal\site_audit\Reports\Cache;

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
    $cache = new Cache();
    $renderer = new Html($cache);

    return [
      '#type' => 'markup',
      '#markup' => $renderer->render(TRUE),
    ];
  }

}
