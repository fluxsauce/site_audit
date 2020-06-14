<?php

namespace Drupal\site_audit\Plugin\SiteAuditReport;

use Drupal\site_audit\Plugin\SiteAuditReportBase;

/**
 * Provides a Content Report.
 *
 * @SiteAuditReport(
 *  id = "content",
 *  name = @Translation("Content"),
 *  description = @Translation("Content Checks")
 * )
 */
class Content extends SiteAuditReportBase {}
