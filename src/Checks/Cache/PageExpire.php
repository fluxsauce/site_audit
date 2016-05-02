<?php
/**
 * @file
 * Contains Drupal\site_audit\Checks\Cache\PageExpire.
 */

namespace Drupal\site_audit\Checks\Cache;

use Drupal\site_audit\Check;

/**
 * Class PageExpire.
 */
class PageExpire extends Check {

  /**
   * {@inheritdoc}.
   */
  public function getLabel() {
    return $this->t('Expiration of cached pages');
  }

  /**
   * {@inheritdoc}.
   */
  public function getDescription() {
    return $this->t("Verify that Drupal's cached pages last for at least 15 minutes.");
  }

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
    return $this->t('Expiration of cached pages is set to @minutes min.', array(
      '@minutes' => round(\Drupal::config('system.performance')->get('cache.page.max_age') / 60),
    ));
  }

  /**
   * {@inheritdoc}.
   */
  public function getResultWarn() {
    return $this->t('Expiration of cached pages only set to @minutes min.', array(
      '@minutes' => round(\Drupal::config('system.performance')->get('cache.page.max_age') / 60),
    ));
  }

  /**
   * {@inheritdoc}.
   */
  public function getAction() {
    if (!in_array($this->score, array(Check::AUDIT_CHECK_SCORE_PASS))) {
      return $this->t('Go to /admin/config/development/performance and set "Expiration of cached pages" to 15 min or above.');
    }
  }

  /**
   * {@inheritdoc}.
   */
  public function calculateScore() {
    $config = \Drupal::config('system.performance')->get('cache.page.max_age');
    if ($config == 0) {
      if (site_audit_env_is_dev()) {
        return Check::AUDIT_CHECK_SCORE_INFO;
      }
      return Check::AUDIT_CHECK_SCORE_FAIL;
    }
    elseif ($config >= 900) {
      return Check::AUDIT_CHECK_SCORE_PASS;
    }
    return Check::AUDIT_CHECK_SCORE_WARN;
  }

}
