<?php

namespace Drupal\site_audit\Plugin\SiteAuditCheck;

use Drupal\site_audit\Plugin\SiteAuditCheckBase;

/**
 * Provides the CachePageExpire Check.
 *
 * @SiteAuditCheck(
 *  id = "cache_page_expire",
 *  name = @Translation("Expiration of cached pages"),
 *  description = @Translation("Verify that Drupal\'s cached pages last for at least 15 minutes."),
 *  report = "cache"
 * )
 */
class CachePageExpire extends SiteAuditCheckBase {

  /**
   * {@inheritdoc}.
   */
  public function getResultFail() {
    return $this->t('Expiration of cached pages not set!');
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
    return $this->t('Expiration of cached pages is set to @minutes min.', [
      '@minutes' => round(\Drupal::config('system.performance')->get('cache.page.max_age') / 60),
    ]);
  }

  /**
   * {@inheritdoc}.
   */
  public function getResultWarn() {
    return $this->t('Expiration of cached pages only set to @minutes min.', [
      '@minutes' => round(\Drupal::config('system.performance')->get('cache.page.max_age') / 60),
    ]);
  }

  /**
   * {@inheritdoc}.
   */
  public function getAction() {}

  /**
   * {@inheritdoc}.
   */
  public function calculateScore() {
    $config = \Drupal::config('system.performance')->get('cache.page.max_age');
    if ($config == 0) {
      if (site_audit_env_is_dev()) {
        return SiteAuditCheckBase::AUDIT_CHECK_SCORE_INFO;
      }
      return SiteAuditCheckBase::AUDIT_CHECK_SCORE_FAIL;
    }
    elseif ($config >= 900) {
      return SiteAuditCheckBase::AUDIT_CHECK_SCORE_PASS;
    }
    return SiteAuditCheckBase::AUDIT_CHECK_SCORE_WARN;
  }

}
