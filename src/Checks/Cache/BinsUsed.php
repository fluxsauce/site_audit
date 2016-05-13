<?php
/**
 * @file
 * Contains Drupal\site_audit\Checks\Cache\BinsUsed.
 */

namespace Drupal\site_audit\Checks\Cache;

use Drupal\site_audit\Check;


/**
 * Class BinsUsed.
 */
class BinsUsed extends Check {

  /**
   * {@inheritdoc}.
   */
  public function getLabel() {
    return $this->t('Used Bins');
  }

  /**
   * {@inheritdoc}.
   */
  public function getDescription() {
    return $this->t('Cache bins used by each service.');
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

    foreach ($this->registry['bins_used'] as $bin => $class) {
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

    foreach ($container->getParameter('cache_bins') as $bin) {
      $backend_class = get_class($container->get('cache.' . $bin)) . 'Factory';
      $backend = array_search($backend_class, $this->registry['bins_all']);
      $this->registry['bins_used'][$bin] = $backend;
    }

    return Check::AUDIT_CHECK_SCORE_INFO;
  }

}
