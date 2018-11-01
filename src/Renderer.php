<?php

namespace Drupal\site_audit;

use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Class Renderer.
 */
abstract class Renderer {
  /**
   * The Report to be rendered.
   *
   * @var \Drupal\site_audit\Report.
   */
  var $report;

  /**
   * The logger we are using for output
   */
  var $logger;

  use StringTranslationTrait;

  public function __construct($report, $logger) {
    $this->report = $report;
    $this->logger = $logger;
  }

  abstract public function render($detail = FALSE);
}
