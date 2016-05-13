<?php
/**
 * @file
 * Contains Drupal\site_audit\Checks\Cache\BinsDefault.
 */

namespace Drupal\site_audit\Checks\Cache;

use Drupal\site_audit\Check;

/**
 * Class BinsDefault.
 */
class BinsDefault extends Check {

  /**
   * {@inheritdoc}.
   */
  public function getLabel() {
    return $this->t('Default cache bins');
  }

  /**
   * {@inheritdoc}.
   */
  public function getDescription() {
    return $this->t('Default bin per service');
  }

  /**
   * {@inheritdoc}.
   */
  public function getResultFail() {}

  /**
   * {@inheritdoc}.
   */
  public function getResultInfo() {
    $ret_val = array(
      'headers' => ['Service', 'Bin'],
    );

    foreach ($this->registry['default_backends'] as $bin => $class) {
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
    $defaults = $container->getParameter('cache_default_bin_backends');
    $this->registry['default_backends'] = array();
    foreach ($container->getParameter('cache_bins') as $bin) {
      if (isset($defaults[$bin])) {
        $this->registry['default_backends'][$bin] = $defaults[$bin];
      }
      else {
        $this->registry['default_backends'][$bin] = 'cache.backend.database';
      }
    }
    return Check::AUDIT_CHECK_SCORE_INFO;
  }

}
