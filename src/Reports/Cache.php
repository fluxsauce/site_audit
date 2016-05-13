<?php
/**
 * @file
 * Contains Drupal\site_audit\Reports\Cache.
 */

namespace Drupal\site_audit\Reports;

use Drupal\site_audit\Report;

/**
 * Class Cache.
 */
class Cache extends Report {
  /**
   * {@inheritdoc}.
   */
  public function getLabel() {
    return $this->t("Drupal's caching settings");
  }

  /**
   * {@inheritdoc}.
   */
  public function getCheckNames() {
    return [
      'BinsAll',
      'BinsDefault',
      'BinsUsed',
      'PageExpire',
      'PreprocessCss',
      'PreprocessJs',
    ];
  }

}
