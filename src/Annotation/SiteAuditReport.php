<?php

namespace Drupal\site_audit\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Site Audit Report item annotation object.
 *
 * @see \Drupal\site_audit\Plugin\SiteAuditReportManager
 * @see plugin_api
 *
 * @Annotation
 */
class SiteAuditReport extends Plugin {


  /**
   * The report ID.
   *
   * @var string
   */
  public $id;

  /**
   * The label/name of the report.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

  /**
   * The description of the report.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $description;

}
