<?php

namespace Drupal\site_audit\Plugin\SiteAuditCheck;

use Drupal\site_audit\Plugin\SiteAuditCheckBase;

/**
 * Provides the CachePreprocessJS Check.
 *
 * @SiteAuditCheck(
 *  id = "cache_preprocess_js",
 *  name = @Translation("Aggregate and compress JavaScript files in Drupal"),
 *  description = @Translation("Verify that Drupal is aggregating JavaScript."),
 *  report = "cache"
 * )
 */
class CachePreprocessJS extends SiteAuditCheckBase {

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
    if (!in_array($this->score, [SiteAuditCheckBase::AUDIT_CHECK_SCORE_PASS])) {
      return $this->t('Go to /admin/config/development/performance and check "Aggregate JavaScript files".');
    }
  }

  /**
   * {@inheritdoc}.
   */
  public function calculateScore() {
    $config = \Drupal::config('system.performance')->get('js.preprocess');
    if ($config) {
      return SiteAuditCheckBase::AUDIT_CHECK_SCORE_PASS;
    }
    if (site_audit_env_is_dev()) {
      return SiteAuditCheckBase::AUDIT_CHECK_SCORE_INFO;
    }
    return SiteAuditCheckBase::AUDIT_CHECK_SCORE_FAIL;
  }

}
