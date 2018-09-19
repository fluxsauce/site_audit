<?php

namespace Drupal\site_audit\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Site Audit Check item annotation object.
 *
 * @see \Drupal\site_audit\Plugin\SiteAuditCheckManager
 * @see plugin_api
 *
 * @Annotation
 */
class SiteAuditCheck extends Plugin {


  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The label/name of the check.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

  /**
   * The description of the check.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $description;

  /**
   * The weight of the check.
   *
   * @var int
   */
  public $weight = 0;

  /**
   * The report for the check.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $report;

}
