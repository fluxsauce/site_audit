<?php

namespace Drupal\site_audit\Plugin\SiteAuditReport;

use Drupal\site_audit\Plugin\SiteAuditReportBase;

/**
 * Provides a Cron Report.
 *
 * @SiteAuditReport(
 *  id = "cron",
 *  name = @Translation("Cron"),
 *  description = @Translation("Drupal's Cron")
 * )
 */
class Cron extends SiteAuditReportBase {}
