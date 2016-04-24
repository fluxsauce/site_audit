<?php
/**
 * @file
 * Contains \SiteAudit\Report\Block.
 */

/**
 * Class SiteAuditReportBlock.
 */
class SiteAuditReportBlock extends SiteAuditReportAbstract {

  /**
   * Implements \SiteAudit\Report\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Block');
  }

}
