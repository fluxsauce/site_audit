<?php

namespace Drupal\site_audit\Renderer;

use Drupal\site_audit\Renderer;

class Json extends Renderer {

  public function render($detail = FALSE) {
    $report = array(
      'percent' => $this->report->getPercent(),
      'label' => $this->report->getLabel(),
      'checks' => array(),
    );
    foreach ($this->report->getChecks() as $check) {
      $report['checks'][get_class($check)] = array(
        'label' => $check->getLabel(),
        'description' => $check->getDescription(),
        'result' => $check->getResult(),
        'action' => $check->renderAction(),
        'score' => $check->getScore(),
      );
    }
    return json_encode($report);
  }
}
