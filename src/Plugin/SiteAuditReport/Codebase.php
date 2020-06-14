<?php

namespace Drupal\site_audit\Plugin\SiteAuditReport;

use Drupal\site_audit\Plugin\SiteAuditReportBase;

/**
 * Provides a Database Report.
 *
 * @SiteAuditReport(
 *  id = "codebase",
 *  name = @Translation("Codebase"),
 *  description = @Translation("Drupal Codebase Best Practices and Settings")
 * )
 */
class Codebase extends SiteAuditReportBase {}
