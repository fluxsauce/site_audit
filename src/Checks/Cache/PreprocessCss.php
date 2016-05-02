<?php
/**
 * @file
 * Contains Drupal\site_audit\Checks\Cache\PreprocessCss.
 */

namespace Drupal\site_audit\Checks\Cache;

use Drupal\site_audit\Check;

/**
 * Class PreprocessCss.
 */
class PreprocessCss extends Check {

  /**
   * {@inheritdoc}.
   */
  public function getLabel() {
    return $this->t('Aggregate and compress CSS files in Drupal');
  }

  /**
   * {@inheritdoc}.
   */
  public function getDescription() {
    return $this->t('Verify that Drupal is aggregating and compressing CSS.');
  }

  /**
   * {@inheritdoc}.
   */
  public function getResultFail() {
    return $this->t('CSS aggregation and compression is not enabled!');
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
    return $this->t('CSS aggregation and compression is enabled.');
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
      return $this->t('Go to /admin/config/development/performance and check "Aggregate and compress CSS files".');
    }
  }

  /**
   * {@inheritdoc}.
   */
  public function calculateScore() {
    $config = \Drupal::config('system.performance')->get('css.preprocess');
    if ($config) {
      return Check::AUDIT_CHECK_SCORE_PASS;
    }
    if (site_audit_env_is_dev()) {
      return Check::AUDIT_CHECK_SCORE_INFO;
    }
    return Check::AUDIT_CHECK_SCORE_FAIL;
  }

}
