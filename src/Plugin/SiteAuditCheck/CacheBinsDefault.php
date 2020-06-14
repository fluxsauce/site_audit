<?php

namespace Drupal\site_audit\Plugin\SiteAuditCheck;

use Drupal\site_audit\Plugin\SiteAuditCheckBase;

/**
 * Provides the CacheBinsDefault. Check.
 *
 * @SiteAuditCheck(
 *  id = "cache_default_bins",
 *  name = @Translation("Default Cache Bins"),
 *  description = @Translation("All default cache bins."),
 *  report = "cache"
 * )
 */
class CacheBinsDefault extends SiteAuditCheckBase {

  /**
   * {@inheritdoc}.
   */
  public function getResultFail() {}

  /**
   * {@inheritdoc}.
   */
  public function getResultInfo() {
    $ret_val = [
      '#header' => ['Bin', 'Class'],
      '#theme' => 'table',
    ];

    foreach ($this->registry->cache_default_backends as $bin => $class) {
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
    $defaults = $container->getParameter('cache_default_bin_backends');
    $this->registry->cache_default_backends = [];
    foreach ($container->getParameter('cache_bins') as $bin) {
      if (isset($defaults[$bin])) {
        $this->registry->cache_default_backends[$bin] = $defaults[$bin];
      }
      else {
        $this->registry->cache_default_backends[$bin] = 'cache.backend.database';
      }
    }
    return SiteAuditCheckBase::AUDIT_CHECK_SCORE_INFO;
  }

}
