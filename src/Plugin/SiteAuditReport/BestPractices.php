<?php

namespace Drupal\site_audit\Plugin\SiteAuditReport;

use Drupal\site_audit\Plugin\SiteAuditReportBase;

/**
 * Provides a BestPractices Report.
 *
 * @SiteAuditReport(
 *  id = "best_practices",
 *  name = @Translation("Best practices"),
 *  description = @Translation("Drupal Best Practices")
 * )
 */
class BestPractices extends SiteAuditReportBase {}
