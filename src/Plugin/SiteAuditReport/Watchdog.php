<?php

namespace Drupal\site_audit\Plugin\SiteAuditReport;

use Drupal\site_audit\Plugin\SiteAuditReportBase;

/**
 * Provides a Views Report.
 *
 * @SiteAuditReport(
 *  id = "watchdog",
 *  name = @Translation("Watchdog database logs"),
 *  description = @Translation("")
 * )
 */
class Watchdog extends SiteAuditReportBase {}
