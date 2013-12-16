<?php
/**
 * @file
 * Contains \SiteAudit\Check\Codebase\SizeAll.
 */

class SiteAuditCheckCodebaseSizeAll extends SiteAuditCheckAbstract {
  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Size of entire site');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Determine the size of the site root; does not include remote mounts.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {
    return dt('Unable to determine size of site root!');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultInfo().
   */
  public function getResultInfo() {
    if ($this->registry['size_all_kb'] < 1024) {
      return dt('Total size: @size_all_kbkB', array(
        '@size_all_kb' => number_format($this->registry['size_all_kb']),
      ));
    }
    return dt('Total size: @size_all_mbMB', array(
      '@size_all_mb' => number_format($this->registry['size_all_kb'] / 1024, 2),
    ));
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultPass().
   */
  public function getResultPass() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getResultWarn().
   */
  public function getResultWarn() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getAction().
   */
  public function getAction() {}

  /**
   * Implements \SiteAudit\Check\Abstract\calculateScore().
   */
  public function calculateScore() {
    $drupal_root = drush_get_context('DRUSH_SELECTED_DRUPAL_ROOT');
    exec('du -s -k -x ' . $drupal_root, $result);
    $this->registry['size_all_kb'] = trim($result[0]);
    if (!$this->registry['size_all_kb']) {
      $this->abort = TRUE;
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL;
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO;
  }
}
