<?php

namespace Drupal\site_audit\Plugin\SiteAuditCheck;

use Drupal\site_audit\Plugin\SiteAuditCheckBase;

/**
 * Provides the CacheBinsUsed Check.
 *
 * @SiteAuditCheck(
 *  id = "cache_bins_used",
 *  name = @Translation("Used Bins"),
 *  description = @Translation("Cache bins used by each service."),
 *  report = "cache"
 * )
 */
class CacheBinsUsed extends SiteAuditCheckBase {

  /**
   * {@inheritdoc}.
   */
  public function getResultFail() {}

  /**
   * {@inheritdoc}.
   */
  public function getResultInfo() {
    $ret_val = [
      '#header' => ['Service', 'Bin'],
      '#theme' => 'table',
    ];

    foreach ($this->registry->cache_bins_used as $bin => $class) {
      $ret_val['#rows'][] = [$bin, $class];
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
    if (empty($this->registry->cache_bins_all)) {
      $container = \Drupal::getContainer();
      $services = $container->getServiceIds();

      $this->registry->cache_bins_all = [];
      $back_ends = preg_grep('/^cache\.backend\./', array_values($services));
      foreach ($back_ends as $backend) {
        $this->registry->cache_bins_all[$backend] = get_class($container->get($backend));
      }
    }

    foreach ($container->getParameter('cache_bins') as $service => $bin) {
      $backend_class = get_class($container->get($service)) . 'Factory';
      $backend = array_search($backend_class, $this->registry->cache_bins_all);
      $this->registry->cache_bins_used[$bin] = $backend;
    }

    return SiteAuditCheckBase::AUDIT_CHECK_SCORE_INFO;
  }

}
