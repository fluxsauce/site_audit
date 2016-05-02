<?php
/**
 * @file
 * Contains Drupal\site_audit\Checks\Cache\BinsAll.
 */

namespace Drupal\site_audit\Checks\Cache;

use Drupal\site_audit\Check;

/**
 * Class BinsAll.
 */
class BinsAll extends Check {

  /**
   * {@inheritdoc}.
   */
  public function getLabel() {
    return $this->t('Available cache bins');
  }

  /**
   * {@inheritdoc}.
   */
  public function getDescription() {
    return $this->t('All available cache bins.');
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
      'headers' => ['Bin', 'Class'],
    );

    foreach ($this->registry['all_backends'] as $bin => $class) {
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
    $this->registry['all_backends'] = [];
    $back_ends = preg_grep('/^cache\.backend\./', array_values($services));
    foreach ($back_ends as $backend) {
      $this->registry['all_backends'][$backend] = get_class($container->get($backend));
    }

    return Check::AUDIT_CHECK_SCORE_INFO;
  }

}
