<?php
/**
 * @file
 * Contains Drupal\site_audit\Renderer.
 */

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

  use StringTranslationTrait;

  public function __construct($report) {
    $this->report = $report;
  }

  abstract public function render($detail = FALSE);
}
