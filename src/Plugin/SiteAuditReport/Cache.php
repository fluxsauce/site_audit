<?php

namespace Drupal\site_audit\Plugin\SiteAuditReport;

use Drupal\site_audit\Plugin\SiteAuditReportBase;

/**
 * Provides a Cache Report.
 *
 * @SiteAuditReport(
 *  id = "cache",
 *  name = @Translation("Cache"),
 *  description = @Translation("Drupal's caching settings")
 * )
 */
class Cache extends SiteAuditReportBase {}
