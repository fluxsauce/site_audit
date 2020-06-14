<?php

namespace Drupal\site_audit\Plugin\SiteAuditCheck;

use Drupal\site_audit\Plugin\SiteAuditCheckBase;

/**
 * Provides the ExtensionsCount Check.
 *
 * @SiteAuditCheck(
 *  id = "extensions_count",
 *  name = @Translation("Count"),
 *  description = @Translation("Count the number of enabled extensions (modules and themes) in a site."),
 *  report = "extensions"
 * )
 */
class ExtensionsCount extends SiteAuditCheckBase {

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
      '@extension_count' => $this->registry->extension_count,
    ]);
  }

  /**
   * {@inheritdoc}.
   */
  public function getResultWarn() {
    return $this->t('There are @extension_count extensions enabled; that\'s higher than the average.', [
      '@extension_count' => $this->registry->extension_count,
    ]);
  }

  /**
   * {@inheritdoc}.
   */
  public function getAction() {
    if ($this->score != SiteAuditCheckBase::AUDIT_CHECK_SCORE_PASS) {
      $ret_val = $this->t('Consider the following options:') . PHP_EOL;
      $options = [];
      $options[] = $this->t('Disable unneeded or unnecessary extensions.');
      $options[] = $this->t('Consolidate functionality if possible, or custom develop a solution specific to your needs.');
      $options[] = $this->t('Avoid using modules that serve only one small purpose that is not mission critical.');

      // If ($this->options['html']) {.
      if (TRUE) {
        $ret_val .= '<ul>';
        foreach ($options as $option) {
          $ret_val .= '<li>' . $option . '</li>';
        }
        $ret_val .= '</ul>';
      }
      else {
        foreach ($options as $option) {
          if (!$this->options['json']) {
            $ret_val .= str_repeat(' ', 6);
          }
          $ret_val .= '- ' . $option . PHP_EOL;
        }
        if (!$this->options['json']) {
          $ret_val .= str_repeat(' ', 6);
        }
      }
      $ret_val .= $this->t('A lightweight site is a fast and happy site!');
      return $ret_val;
    }
  }

  /**
   * {@inheritdoc}.
   */
  public function calculateScore() {
    if (!isset($this->registry->extensions) || empty($this->registry->extensions)) {
      $moduleHandler = \Drupal::service('module_handler');
      $this->registry->extensions = $moduleHandler->getModuleList();
    }

    $this->registry->extension_count = count($this->registry->extensions);

    if ($this->registry->extension_count >= 150) {
      return SiteAuditCheckBase::AUDIT_CHECK_SCORE_WARN;
    }
    return SiteAuditCheckBase::AUDIT_CHECK_SCORE_PASS;
  }

}
