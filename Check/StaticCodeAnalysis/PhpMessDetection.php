<?php
/**
 * @file
 * Contains \SiteAudit\Check\StaticCodeAnalysis\PhpMessDetection.
 */

/**
 * Class SiteAuditCheckStaticCodeAnalysisPhpMessDetection.
 */
class SiteAuditCheckStaticCodeAnalysisPhpMessDetection extends SiteAuditCheckAbstract {
  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('PHP Mess Detection Violations');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Run phpmd on custom code.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {
    return dt('Unable to find phpmd. Please run "composer install" to install all the dependencies of site_audit.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultInfo().
   */
  public function getResultInfo() {
    $ret_val = '';

    if (empty($this->registry['phpmd_out'])) {
      if (drush_get_option('detail')) {
        return dt('No phpmd violations.');
      }
      return $ret_val;
    }
    if (drush_get_option('html') == TRUE) {
      $ret_val .= '<table class="table table-condensed">';
      $ret_val .= '<thead><tr><th>' . dt('Line') . '</th><th>' . dt('Type') . '</th><th>' . dt('Action') . '</th></tr></thead>';
      foreach ($this->registry['phpmd_out'] as $filename => $violations) {
        $ret_val .= "<tr align='center'><td colspan='3'><a href='file:///$filename'>File: $filename</a></td></tr>";
        foreach ($violations as $violation) {
          $begin = $violation['beginline'];
          $end = $violation['endline'];
          $rule = $violation['rule'];
          $url = $violation['externalInfoUrl'];
          $ret_val .= "<tr><td>$begin to $end</td><td><a href='$url'>$rule</a></td><td>$violation</td></tr>";
        }
      }
      $ret_val .= '</table>';
    }
    else {
      $rows = 0;
      foreach ($this->registry['phpmd_out'] as $filename => $violations) {
        if ($rows++ > 0) {
          $ret_val .= PHP_EOL;
          if (!drush_get_option('json')) {
            $ret_val .= str_repeat(' ', 4);
          }
        }
        $ret_val .= dt('Filename: @filename, Violations: @total', array(
          '@filename' => $filename,
          '@total' => count($violations),
        ));
        foreach ($violations as $violation) {
          $ret_val .= PHP_EOL;
          if (!drush_get_option('json')) {
            $ret_val .= str_repeat(' ', 6);
          }
          $begin = $violation['beginline'];
          $end = $violation['endline'];
          $rule = $violation['rule'];
          $ret_val .= "$begin to $end: $rule : $violation";
        }
      }
    }
    return $ret_val;

  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultPass().
   */
  public function getResultPass() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getResultWarn().
   */
  public function getResultWarn() {
    return dt('No custom code path specified');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getAction().
   */
  public function getAction() {}

  /**
   * Implements \SiteAudit\Check\Abstract\calculateScore().
   */
  public function calculateScore() {
    // Check if phpmd exists.
    if (!is_file(SITE_AUDIT_BASE_PATH . '/vendor/bin/phpmd') && empty(exec('which phpmd 2>/dev/null'))) {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL;
    }
    // Get the custom code paths.
    $custom_code = \Drupal::config('site_audit')->get('custom_code');
    if ($custom_code == NULL) {
      $custom_code = drush_get_option('custom_code', '');
      if (empty($custom_code)) {
        return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN;
      }
      $custom_code = explode(',', $custom_code);
    }
    $phpmd_path = "";
    // Get the path of phpmd executable.
    if (is_file(SITE_AUDIT_BASE_PATH . '/vendor/bin/phpmd')) {
      $phpmd_path = SITE_AUDIT_BASE_PATH . '/vendor/bin/phpmd';
    }
    else {
      $phpmd_path = exec('which phpmd 2>/dev/null');
    }
    // Supress XML errors which will be handled by try catch instead.
    libxml_use_internal_errors(TRUE);

    $phpmd_rules = drush_get_option('phpmd_rules', 'codesize,naming,design,unusedcode');
    foreach ($custom_code as $path) {
      $output = array();
      $exit_code = 0;
      $command = $phpmd_path . ' ' . $path . ' xml ' . $phpmd_rules;
      exec($command, $output, $exit_code);
      if ($exit_code == 1) {
        continue;
      }
      try {
        $output = new SimpleXMLElement(implode("\n", $output));
        foreach ($output as $file) {
          foreach ($file as $violation) {
            $this->registry['phpmd_out'][(String) $file[0]['name']][] = $violation;
          }
        }
      }
      catch (Exception $e) {
        continue;
      }
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO;
  }

}
