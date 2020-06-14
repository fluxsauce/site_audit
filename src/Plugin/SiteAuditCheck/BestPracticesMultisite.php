<?php

namespace Drupal\site_audit\Plugin\SiteAuditCheck;

use Drupal\site_audit\Plugin\SiteAuditCheckBase;

/**
 * Provides the BestPracticesMultisite Check.
 *
 * @SiteAuditCheck(
 *  id = "best_practices_multisite",
 *  name = @Translation("Multi-site"),
 *  description = @Translation("Detect multi-site configurations."),
 *  report = "best_practices"
 * )
 */
class BestPracticesMultisite extends SiteAuditCheckBase {

  /**
   * {@inheritdoc}.
   */
  public function getResultFail() {
    return $this->t('The following multi-site configuration(s) were detected: @list', [
      '@list' => implode(', ', $this->registry->multisites),
    ]);
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
    return $this->t('No multi-sites detected.');
  }

  /**
   * {@inheritdoc}.
   */
  public function getResultWarn() {
    if ($this->registry->multisite_enabled) {
      return $this->t('sites/sites.php is present but no multisite directories are present.');
    }
    else {
      return $this->t('Multisite directories are present but sites/sites.php is not present.');
    }
  }

  /**
   * {@inheritdoc}.
   */
  public function getAction() {
    if ($this->score == SiteAuditCheckBase::AUDIT_CHECK_SCORE_FAIL) {
      return $this->t('See https://pantheon.io/blog/drupal-multisite-much-ado-about-drupal-multisite for details.');
    }
    if ($this->score == SiteAuditCheckBase::AUDIT_CHECK_SCORE_WARN) {
      if ($this->registry->multisite_enabled) {
        return dt('See https://www.drupal.org/node/2297419 for details on how to use multisite feature in Drupal 8.');
      }
      else {
        return $this->t('Inside the sites/ directory, copy example.sites.php to sites.php to create the configuration. See https://www.drupal.org/node/2297419 for details.');
      }
    }
  }

  /**
   * {@inheritdoc}.
   */
  public function calculateScore() {
    $handle = opendir(DRUPAL_ROOT . '/sites/');
    $this->registry->multisites = [];
    while (FALSE !== ($entry = readdir($handle))) {
      if (!in_array($entry, [
        '.',
        '..',
        'default',
        'all',
        'example.sites.php',
        'README.txt',
        '.svn',
        '.DS_Store',
      ])
      ) {
        if (is_dir(DRUPAL_ROOT . '/sites/' . $entry)) {
          $this->registry->multisites[] = $entry;
        }
      }
    }
    closedir($handle);
    if (isset($this->registry->multisite_enabled) && !empty($this->registry->multisite_enabled)) {
      if ($this->options['vendor'] == 'pantheon') {
        return SiteAuditCheckBase::AUDIT_CHECK_SCORE_FAIL;
      }
      if (!empty($this->registry->multisites)) {
        return SiteAuditCheckBase::AUDIT_CHECK_SCORE_INFO;
      }
      return SiteAuditCheckBase::AUDIT_CHECK_SCORE_WARN;
    }
    elseif (!empty($this->registry->multisites)) {
      return SiteAuditCheckBase::AUDIT_CHECK_SCORE_WARN;
    }
    return SiteAuditCheckBase::AUDIT_CHECK_SCORE_PASS;
  }

}
