<?php
/**
 * @file
 * Contains Drupal\site_audit\Checks\Cache\PreprocessJs.
 */

namespace Drupal\site_audit\Checks\Cache;

use Drupal\site_audit\Check;

/**
 * Class PreprocessJs.
 */
class PreprocessJs extends Check {

  /**
   * {@inheritdoc}.
   */
  public function getLabel() {
    return $this->t('Aggregate JavaScript files in Drupal');
  }

  /**
   * {@inheritdoc}.
   */
  public function getDescription() {
    return $this->t('Verify that Drupal is aggregating JavaScript.');
  }

  /**
   * {@inheritdoc}.
   */
  public function getResultFail() {
    return $this->t('JavaScript aggregation is not enabled!');
  }

  /**
   * {@inheritdoc}.
   */
  public function getResultInfo() {
    return $this->getResultFail();
  }

  /**
   * {@inheritdoc}.
   */
  public function getResultPass() {
    return $this->t('JavaScript aggregation is enabled.');
  }

  /**
   * {@inheritdoc}.
   */
  public function getResultWarn() {}

  /**
   * {@inheritdoc}.
   */
  public function getAction() {
    if (!in_array($this->score, array(Check::AUDIT_CHECK_SCORE_PASS))) {
      return $this->t('Go to /admin/config/development/performance and check "Aggregate JavaScript files".');
    }
  }

  /**
   * {@inheritdoc}.
   */
  public function calculateScore() {
    $config = \Drupal::config('system.performance')->get('js.preprocess');
    if ($config) {
      return Check::AUDIT_CHECK_SCORE_PASS;
    }
    if (site_audit_env_is_dev()) {
      return Check::AUDIT_CHECK_SCORE_INFO;
    }
    return Check::AUDIT_CHECK_SCORE_FAIL;
  }

}
