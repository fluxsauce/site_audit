<?php
/**
 * @file
 * Contains \AuditCheckExtensionsCount.
 */

class AuditCheckExtensionsCount extends AuditCheck {
  /**
   * Implements \AuditCheck\getLabel().
   */
  public function getLabel() {
    return dt('Count');
  }

  /**
   * Implements \AuditCheck\getDescription().
   */
  public function getDescription() {
    return dt('Count the number of enabled extensions (modules and themes) in a site.');
  }

  /**
   * Implements \AuditCheck\getResultFail().
   */
  public function getResultFail() {}

  /**
   * Implements \AuditCheck\getResultInfo().
   */
  public function getResultInfo() {}

  /**
   * Implements \AuditCheck\getResultPass().
   */
  public function getResultPass() {
    return dt('There are @extension_count extensions enabled.', array(
      '@extension_count' => $this->registry['extension_count'],
    ));
  }

  /**
   * Implements \AuditCheck\getResultWarning().
   */
  public function getResultWarning() {
    return dt('There are @extension_count extensions enabled; that\'s higher than the average.', array(
      '@extension_count' => $this->registry['extension_count'],
    ));
  }

  /**
   * Implements \AuditCheck\getAction().
   */
  public function getAction() {
    if ($this->score != AuditCheck::AUDIT_CHECK_SCORE_PASS) {
      $output = array();
      $output[] = dt('Consider the following options:');
      $output[] = '    - ' . dt('Disable unneeded or unnecessary extensions.');
      $output[] = '    - ' . dt('Consolidate functionality if possible, or custom develop a solution specific to your needs.');
      $output[] = '    - ' . dt('Avoid using modules that serve only one small purpose that is not mission critical.');
      $output[] = dt('A lightweight site is a fast and happy site!');
      if ($this->html) {
        return implode('<br/>', $output);
      }
      return implode(PHP_EOL, $output);
    }
  }

  /**
   * Implements \AuditCheck\getScore().
   */
  public function getScore() {
    $this->registry['extension_count'] = 0;
    $extension_info = drush_get_extensions(FALSE);

    foreach ($extension_info as $key => $extension) {
      $status = drush_get_extension_status($extension);
      if (!in_array($status, array('enabled'))) {
        unset($extension_info[$key]);
        continue;
      }
      $this->registry['extension_count']++;
    }

    if ($this->registry['extension_count'] >= 150) {
      return AuditCheck::AUDIT_CHECK_SCORE_WARN;
    }
    return AuditCheck::AUDIT_CHECK_SCORE_PASS;
  }
}
