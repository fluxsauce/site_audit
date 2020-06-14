<?php

namespace Drupal\site_audit\Plugin\SiteAuditCheck;

use Drupal\site_audit\Plugin\SiteAuditCheckBase;

/**
 * Provides the CronLast Check.
 *
 * @SiteAuditCheck(
 *  id = "codebase_sizeall",
 *  name = @Translation("Size of entire site"),
 *  description = @Translation("Determine the size of the site root; does not include remote mounts."),
 *  report = "codebase",
 *  weight = -1,
 * )
 */
class CodebaseSizeAll extends SiteAuditCheckBase {

  /**
   * {@inheritdoc}.
   */
  public function getResultFail() {
    return $this->t('Empty, or unable to determine the size due to a permission error.');
  }

  /**
   * {@inheritdoc}.
   */
  public function getResultInfo() {

    if ($this->registry->size_all_kb < 1024) {
      return $this->t('Total size: @size_all_kbkB', [
        '@size_all_kb' => number_format($this->registry->size_all_kb),
      ]);
    }
    return $this->t('Total size: @size_all_mbMB', [
      '@size_all_mb' => number_format($this->registry->size_all_kb / 1024, 2),
    ]);
  }

  /**
   * {@inheritdoc}.
   */
  public function getResultPass() {}

  /**
   * {@inheritdoc}.
   */
  public function getResultWarn() {}

  /**
   * {@inheritdoc}.
   */
  public function getAction() {}

  /**
   * {@inheritdoc}.
   */
  public function calculateScore() {
    try {
      exec('du -s -k -x ' . DRUPAL_ROOT, $result);
      $this->registry->size_all_kb = trim($result[0]);
      if (!$this->registry->size_all_kb) {
        $this->abort = TRUE;
        return SiteAuditCheckBase::AUDIT_CHECK_SCORE_FAIL;
      }
      return SiteAuditCheckBase::AUDIT_CHECK_SCORE_INFO;
    }
    catch (Exception $e) {
      return SiteAuditCheckBase::AUDIT_CHECK_SCORE_FAIL;
    }
  }

}
