<?php
/**
 * @file
 * Contains \SiteAudit\Check\BestPractices\SitesSuperfluous.
 */

class SiteAuditCheckBestPracticesSitesSuperfluous extends SiteAuditCheckAbstract {
  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Superfluous files in /sites');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Detect unnecessary files.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getResultInfo().
   */
  public function getResultInfo() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getResultPass().
   */
  public function getResultPass() {
    return dt('No unnecessary files detected.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultWarn().
   */
  public function getResultWarn() {
    return dt('The following extra files were detected: @list', array(
      '@list' => implode(', ', $this->registry['superfluous']),
    ));
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getAction().
   */
  public function getAction() {
    if ($this->score == SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN) {
      return dt('Unless you have an explicit need for it, don\'t store anything other than settings here.');
    }
  }

  /**
   * Implements \SiteAudit\Check\Abstract\calculateScore().
   */
  public function calculateScore() {
    $drupal_root = drush_get_context('DRUSH_SELECTED_DRUPAL_ROOT');
    $handle = opendir($drupal_root . '/sites/');
    $this->registry['superfluous'] = array();
    while (FALSE !== ($entry = readdir($handle))) {
      if (!in_array($entry, array(
        '.',
        '..',
        'default',
        'all',
        'example.sites.php',
        'README.txt',
        '.DS_Store',
      ))) {
        if (is_file($drupal_root . '/sites/' . $entry)) {
          // Support multi-site directory aliasing for non-Pantheon sites.
          if ($entry != 'sites.php' || drush_get_option('vendor') == 'pantheon') {
            $this->registry['superfluous'][] = $entry;
          }
        }
      }
    }
    closedir($handle);
    if (!empty($this->registry['superfluous'])) {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN;
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS;
  }
}
