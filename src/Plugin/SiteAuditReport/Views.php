<?php

namespace Drupal\site_audit\Plugin\SiteAuditReport;

use Drupal\site_audit\Plugin\SiteAuditReportBase;

/**
 * Provides a Views Report.
 *
 * @SiteAuditReport(
 *  id = "views",
 *  name = @Translation("Views"),
 *  description = @Translation("Views Best Practices")
 * )
 */
class Views extends SiteAuditReportBase {}
