<?php

namespace Drupal\site_audit\Plugin\SiteAuditCheck;

use Drupal\site_audit\Plugin\SiteAuditCheckBase;

/**
 * Provides the CachePreprocessCSS Check.
 *
 * @SiteAuditCheck(
 *  id = "cache_preprocess_css",
 *  name = @Translation("Aggregate and compress CSS files in Drupal."),
 *  description = @Translation("Verify that Drupal is aggregating and compressing CSS."),
 *  report = "cache"
 * )
 */
class CachePreprocessCSS extends SiteAuditCheckBase {

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
    if (!in_array($this->score, [SiteAuditCheckBase::AUDIT_CHECK_SCORE_PASS])) {
      return $this->t('Go to /admin/config/development/performance and check "Aggregate and compress CSS files".');
    }
  }

  /**
   * {@inheritdoc}.
   */
  public function calculateScore() {
    $config = \Drupal::config('system.performance')->get('css.preprocess');
    if ($config) {
      return SiteAuditCheckBase::AUDIT_CHECK_SCORE_PASS;
    }
    if (site_audit_env_is_dev()) {
      return SiteAuditCheckBase::AUDIT_CHECK_SCORE_INFO;
    }
    return SiteAuditCheckBase::AUDIT_CHECK_SCORE_FAIL;
  }

}
