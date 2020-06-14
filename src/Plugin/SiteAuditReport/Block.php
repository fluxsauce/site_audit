<?php

namespace Drupal\site_audit\Plugin\SiteAuditReport;

use Drupal\site_audit\Plugin\SiteAuditReportBase;

/**
 * Provides a Block Report.
 *
 * @SiteAuditReport(
 *  id = "block",
 *  name = @Translation("Block"),
 *  description = @Translation("Drupal's Blocks")
 * )
 */
class Block extends SiteAuditReportBase {}
