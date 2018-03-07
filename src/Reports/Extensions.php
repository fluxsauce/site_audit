<?php

namespace Drupal\site_audit\Reports;

use Drupal\site_audit\Report;

/**
 * Class Extensions.
 */
class Extensions extends Report {
  /**
   * {@inheritdoc}.
   */
  public function getLabel() {
    return $this->t("Drupal extensions");
  }

  /**
   * {@inheritdoc}.
   */
  public function getCheckNames() {
    return [
      'Count',
    ];
  }

}
