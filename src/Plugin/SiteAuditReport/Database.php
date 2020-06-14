<?php

namespace Drupal\site_audit\Plugin\SiteAuditReport;

use Drupal\site_audit\Plugin\SiteAuditReportBase;

/**
 * Provides a Database Report.
 *
 * @SiteAuditReport(
 *  id = "database",
 *  name = @Translation("Database"),
 *  description = @Translation("Drupal Database Best Practices and Settings")
 * )
 */
class Database extends SiteAuditReportBase {}
