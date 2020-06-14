<?php

namespace Drupal\site_audit\Renderer;

use Drupal\site_audit\Renderer;

/**
 *
 */
class Json extends Renderer {

  /**
   *
   */
  public function render($detail = FALSE) {
    $report = [
      'percent' => $this->report->getPercent(),
      'label' => $this->report->getLabel(),
      'checks' => [],
    ];
    foreach ($this->report->getCheckObjects() as $check) {
      $report['checks'][get_class($check)] = [
        'label' => $check->getLabel(),
        'description' => $check->getDescription(),
        'result' => $check->getResult(),
        'action' => $check->renderAction(),
        'score' => $check->getScore(),
      ];
    }
    return json_encode($report);
  }

}
