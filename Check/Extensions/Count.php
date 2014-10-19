<?php
/**
 * @file
 * Contains \SiteAudit\Check\Extensions\Count.
 */

class SiteAuditCheckExtensionsCount extends SiteAuditCheckAbstract {
  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Count');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Count the number of enabled extensions (modules and themes) in a site.');
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
    return dt('There are @extension_count extensions enabled.', array(
      '@extension_count' => $this->registry['extension_count'],
    ));
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultWarn().
   */
  public function getResultWarn() {
    return dt('There are @extension_count extensions enabled; that\'s higher than the average.', array(
      '@extension_count' => $this->registry['extension_count'],
    ));
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getAction().
   */
  public function getAction() {
    if ($this->score != SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS) {
      $ret_val = dt('Consider the following options:') . PHP_EOL;
      $options = array();
      $options[] = dt('Disable unneeded or unnecessary extensions.');
      $options[] = dt('Consolidate functionality if possible, or custom develop a solution specific to your needs.');
      $options[] = dt('Avoid using modules that serve only one small purpose that is not mission critical.');

      if (drush_get_option('html')) {
        $ret_val .= '<ul>';
        foreach ($options as $option) {
          $ret_val .= '<li>' . $option . '</li>';
        }
        $ret_val .= '</ul>';
      }
      else {
        foreach ($options as $option) {
          if (!drush_get_option('json')) {
            $ret_val .= str_repeat(' ', 6);
          }
          $ret_val .= '- ' . $option . PHP_EOL;
        }
        if (!drush_get_option('json')) {
          $ret_val .= str_repeat(' ', 6);
        }
      }
      $ret_val .= dt('A lightweight site is a fast and happy site!');
      return $ret_val;
    }
  }

  /**
   * Implements \SiteAudit\Check\Abstract\calculateScore().
   */
  public function calculateScore() {
    $this->registry['extension_count'] = 0;
    $this->registry['extensions'] = drush_get_extensions(FALSE);

    foreach ($this->registry['extensions'] as $extension) {
      $status = drush_get_extension_status($extension);
      if (!in_array($status, array('enabled'))) {
        continue;
      }
      $this->registry['extension_count']++;
    }

    if ($this->registry['extension_count'] >= drush_get_option('extension_count', 150)) {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN;
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS;
  }
}
