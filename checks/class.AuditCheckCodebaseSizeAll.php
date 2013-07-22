<?php
/**
 * @file
 * Contains \AuditCheckCodebaseSizeAll.
 */

class AuditCheckCodebaseSizeAll extends AuditCheck {
  /**
   * Implements \AuditCheck\getLabel().
   */
  public function getLabel() {
    return dt('Size of entire Drupal site');
  }

  /**
   * Implements \AuditCheck\getDescription().
   */
  public function getDescription() {
    return dt('Determine the size of the codebase.');
  }

  /**
   * Implements \AuditCheck\getResultFail().
   */
  public function getResultFail() {
    return dt('Unable to determine size of codebase!');
  }

  /**
   * Implements \AuditCheck\getResultInfo().
   */
  public function getResultInfo() {
    return dt('Total size: @size_all_mbMB', array(
      '@size_all_mb' => number_format($this->registry['size_all_mb']),
    ));
  }

  /**
   * Implements \AuditCheck\getResultPass().
   */
  public function getResultPass() {}

  /**
   * Implements \AuditCheck\getResultWarning().
   */
  public function getResultWarning() {}

  /**
   * Implements \AuditCheck\getAction().
   */
  public function getAction() {}

  /**
   * Implements \AuditCheck\getScore().
   */
  public function getScore() {
    $drupal_root = drush_get_context('DRUSH_SELECTED_DRUPAL_ROOT');
    exec('du -s -k -x ' . $drupal_root, $result);
    $kb_size_everything = trim($result[0]);
    $this->registry['size_all_mb'] = round($kb_size_everything / 1024, 2);
    if (!$this->registry['size_all_mb']) {
      $this->abort = TRUE;
      return AuditCheck::AUDIT_CHECK_SCORE_FAIL;
    }
    return AuditCheck::AUDIT_CHECK_SCORE_INFO;
  }
}
