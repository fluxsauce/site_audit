<?php

namespace Drupal\site_audit\Plugin\SiteAuditReport;

use Drupal\site_audit\Plugin\SiteAuditReportBase;

/**
 * Provides an Extensions Report.
 *
 * @SiteAuditReport(
 *  id = "extensions",
 *  name = @Translation("Extensions"),
 *  description = @Translation("Drupal's Extensions")
 * )
 */
class Extensions extends SiteAuditReportBase {}
