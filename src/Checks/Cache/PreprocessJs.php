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
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return $this->t('Aggregate JavaScript files in Drupal');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return $this->t('Verify that Drupal is aggregating JavaScript.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {
    return $this->t('JavaScript aggregation is not enabled!');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultInfo().
   */
  public function getResultInfo() {
    return $this->getResultFail();
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultPass().
   */
  public function getResultPass() {
    return $this->t('JavaScript aggregation is enabled.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultWarn().
   */
  public function getResultWarn() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getAction().
   */
  public function getAction() {
    if (!in_array($this->score, array(Check::AUDIT_CHECK_SCORE_PASS))) {
      return $this->t('Go to /admin/config/development/performance and check "Aggregate JavaScript files".');
    }
  }

  /**
   * Implements \SiteAudit\Check\Abstract\calculateScore().
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
