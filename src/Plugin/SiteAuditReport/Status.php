<?php
/**
 * @file
 * Contains Drupal\site_audit\Plugin\SiteAuditReport\Status
 */

namespace Drupal\site_audit\Plugin\SiteAuditReport;

use Drupal\site_audit\Plugin\SiteAuditReportBase;

/**
 * Provides a Status Report.
 *
 * @SiteAuditReport(
 *  id = "status",
 *  name = @Translation("Status Report"),
 *  description = @Translation("")
 * )
 */
class Status extends SiteAuditReportBase {}