<?php

namespace Drupal\site_audit\Checks\Extensions;

use Drupal\site_audit\Check;

/**
 * Class SiteAuditCheckExtensionsCount.
 */
class Count extends Check {

  /**
   * {@inheritdoc}.
   */
  public function getLabel() {
    return $this->t('Count');
  }

  /**
   * {@inheritdoc}.
   */
  public function getDescription() {
    return $this->t('Count the number of enabled extensions (modules and themes) in a site.');
  }

  /**
   * {@inheritdoc}.
   */
  public function getResultFail() {}

  /**
   * {@inheritdoc}.
   */
  public function getResultInfo() {}

  /**
   * {@inheritdoc}.
   */
  public function getResultPass() {
    return $this->t('There are @extension_count extensions enabled.', [
      '@extension_count' => $this->registry['extension_count'],
    ]);
  }

  /**
   * {@inheritdoc}.
   */
  public function getResultWarn() {
    return $this->t('There are @extension_count extensions enabled; that\'s higher than the average.', [
      '@extension_count' => $this->registry['extension_count'],
    ]);
  }

  /**
   * {@inheritdoc}.
   */
  public function getAction() {
    if (!in_array($this->score, [Check::AUDIT_CHECK_SCORE_PASS])) {
      return $this->t('Consider disabling unneeded or unnecessary extensions, consolidating functionality, developing a solution specific to your needs.') . PHP_EOL;
    }
  }

  /**
   * {@inheritdoc}.
   */
  public function calculateScore() {
    $this->registry['extensions'] = \Drupal::moduleHandler()->getModuleList();
    $this->registry['extension_count'] = count($this->registry['extensions']);

    if ($this->registry['extension_count'] >= 150) {
      return Check::AUDIT_CHECK_SCORE_WARN;
    }

    return Check::AUDIT_CHECK_SCORE_PASS;
  }

}
