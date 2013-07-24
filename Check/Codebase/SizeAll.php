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
    return dt('Size of entire Drupal site');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Determine the size of the codebase.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {
    return dt('Unable to determine size of codebase!');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultInfo().
   */
  public function getResultInfo() {
    return dt('Total size: @size_all_mbMB', array(
      '@size_all_mb' => number_format($this->registry['size_all_mb']),
    ));
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultPass().
   */
  public function getResultPass() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getResultWarning().
   */
  public function getResultWarning() {}

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
    $kb_size_everything = trim($result[0]);
    $this->registry['size_all_mb'] = round($kb_size_everything / 1024, 2);
    if (!$this->registry['size_all_mb']) {
      $this->abort = TRUE;
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL;
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO;
  }
}
