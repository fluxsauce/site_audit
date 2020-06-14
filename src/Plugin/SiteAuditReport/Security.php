<?php

namespace Drupal\site_audit\Plugin\SiteAuditReport;

use Drupal\site_audit\Plugin\SiteAuditReportBase;

/**
 * Provides a Security Report.
 *
 * @SiteAuditReport(
 *  id = "security",
 *  name = @Translation("Security"),
 *  description = @Translation("Security settings and recmomendations")
 * )
 */
class Security extends SiteAuditReportBase {}
