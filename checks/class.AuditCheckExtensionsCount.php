<?php

class AuditCheckExtensionsCount extends AuditCheck {
  protected $_count = 0;

  public function getLabel() {
    return dt('Count');
  }

  public function getResultFail() {}

  public function getResultInfo() {}

  public function getResultPass() {
    return dt('There are @count extensions enabled.', array(
      '@count' => $this->_count,
    ));
  }

  public function getResultWarning() {
    return dt('There are @count extensions enabled; that\'s higher than the average.', array(
      '@count' => $this->_count,
    ));
  }

  public function getAction() {
    if ($this->score != AuditCheck::AUDIT_CHECK_SCORE_PASS) {
      $output = array();
      $output[] = dt('Consider the following options:');
      $output[] = '    - ' . dt('Disable unneeded or unnecessary extensions.');
      $output[] = '    - ' . dt('Consolidate functionality if possible, or custom develop a solution specific to your needs.');
      $output[] = '    - ' . dt('Avoid using modules that serve only one small purpose that is not mission critical.');
      $output[] = dt('A lightweight site is a fast and happy site!');
      if (drush_get_option('html')) {
        $this->html = TRUE;
        return implode('<br/>', $output);
      }
      return implode(PHP_EOL, $output);
    }
  }

  public function getDescription() {
    return dt('Count the number of enabled extensions (modules and themes) in a site.');
  }

  public function getScore() {
    $extension_info = drush_get_extensions(FALSE);

    foreach ($extension_info as $key => $extension) {
      $status = drush_get_extension_status($extension);
      if (!in_array($status, array('enabled'))) {
        unset($extension_info[$key]);
        continue;
      }
      $this->_count++;
    }

    if ($this->_count >= 150) {
      return AuditCheck::AUDIT_CHECK_SCORE_WARN;
    }
    return AuditCheck::AUDIT_CHECK_SCORE_PASS;
  }
}