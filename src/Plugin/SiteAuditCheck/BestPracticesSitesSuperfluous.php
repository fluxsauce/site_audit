<?php

namespace Drupal\site_audit\Plugin\SiteAuditCheck;

use Drupal\site_audit\Plugin\SiteAuditCheckBase;

/**
 * Provides the BestPracticesSitesSuperfluous Check.
 *
 * @SiteAuditCheck(
 *  id = "best_practices_fast_404",
 *  name = @Translation("Superfluous files in /sites"),
 *  description = @Translation("Detect unnecessary files."),
 *  report = "best_practices"
 * )
 */
class BestPracticesSitesSuperfluous extends SiteAuditCheckBase {

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
    return $this->t('No unnecessary files detected.');
  }

  /**
   * {@inheritdoc}.
   */
  public function getResultWarn() {
    return $this->t('The following extra files were detected: @list', [
      '@list' => implode(', ', $this->registry->superfluous),
    ]);
  }

  /**
   * {@inheritdoc}.
   */
  public function getAction() {
    if ($this->score == SiteAuditCheckBase::AUDIT_CHECK_SCORE_WARN) {
      return $this->t('Unless you have an explicit need for it, don\'t store anything other than settings here.');
    }
  }

  /**
   * {@inheritdoc}.
   */
  public function calculateScore() {
    $handle = opendir(DRUPAL_ROOT . '/sites/');
    $this->registry->superfluous = [];
    while (FALSE !== ($entry = readdir($handle))) {
      if (!in_array($entry, [
        '.',
        '..',
        'default',
        'all',
        'example.sites.php',
        'development.services.yml',
        'example.settings.local.php',
        'README.txt',
        '.DS_Store',
      ])) {
        if (is_file(DRUPAL_ROOT . '/sites/' . $entry)) {
          // Support multi-site directory aliasing for non-Pantheon sites.
          if ($entry != 'sites.php' || $this->options['vendor'] == 'pantheon') {
            $this->registry->superfluous[] = $entry;
          }
        }
      }
    }
    closedir($handle);
    if (!empty($this->registry->superfluous)) {
      return SiteAuditCheckBase::AUDIT_CHECK_SCORE_WARN;
    }
    return SiteAuditCheckBase::AUDIT_CHECK_SCORE_PASS;
  }

}
