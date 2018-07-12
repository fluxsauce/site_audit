<?php
/**
 * @file
 * Contains Drupal\site_audit\Plugin\SiteAuditCheck\CacheBinsAll
 */

namespace Drupal\site_audit\Plugin\SiteAuditCheck;

use Drupal\site_audit\Plugin\SiteAuditCheckBase;

/**
 * Provides the CacheBinsAll Check.
 *
 * @SiteAuditCheck(
 *  id = "cache_bins_all",
 *  name = @Translation("Available cache bins"),
 *  description = @Translation("All available cache bins."),
 *  report = "cache"
 * )
 */
class CacheBinsAll extends SiteAuditCheckBase {

  /**
   * {@inheritdoc}.
   */
  public function getResultFail() {}

  /**
   * {@inheritdoc}.
   */
  public function getResultInfo() {
    $ret_val = array(
      'headers' => ['Bin', 'Class'],
    );

    foreach ($this->registry->bins_all as $bin => $class) {
      $ret_val['rows'][] = [$bin, $class];
    }

    return $ret_val;
  }

  /**
   * {@inheritdoc}.
   */
  public function getResultPass() {}

  /**
   * {@inheritdoc}.
   */
  public function getResultWarn() {}

  /**
   * {@inheritdoc}.
   */
  public function getAction() {}

  /**
   * {@inheritdoc}.
   */
  public function calculateScore() {
    $container = \Drupal::getContainer();
    $services = $container->getServiceIds();

    $this->registry->bins_all = [];
    $back_ends = preg_grep('/^cache\.backend\./', array_values($services));
    foreach ($back_ends as $backend) {
      $this->registry->bins_all[$backend] = get_class($container->get($backend));
    }

    return SiteAuditCheckBase::AUDIT_CHECK_SCORE_INFO;
  }

}