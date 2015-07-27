<?php
/**
 * @file
 * Contains \SiteAudit\Check\Codebase\PhpMessDetection.
 */

/**
 * Class SiteAuditCheckCodebasePhpMessDetection.
 */
class SiteAuditCheckCodebasePhpMessDetection extends SiteAuditCheckAbstract {
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
  public function getResultFail() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getResultInfo().
   */
  public function getResultInfo() {
    if (isset($this->registry['phpmd_path'])) {
      return dt('Cannot find phpmd in path. Make sure that phpmd is present in the PATH or run composer install inside the site_audit directory to install all the dependencies');
    }
    return dt('No custom code path specified');
  }


  /**
   * Implements \SiteAudit\Check\Abstract\getResultPass().
   */
  public function getResultPass() {
    dt('Custom Code does not violate any PHP Mess Detector rule.');
  }


  /**
   * Implements \SiteAudit\Check\Abstract\getResultWarn().
   */
  public function getResultWarn() {
    $ret_val = '';
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
          $action = trim((String) $violation);
          $ret_val .= "$begin to $end : $rule - $action";
        }
      }
    }
    return $ret_val;

  }

  /**
   * Implements \SiteAudit\Check\Abstract\getAction().
   */
  public function getAction() {
    dt('Fix the PHP Mess Detector violations.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\calculateScore().
   */
  public function calculateScore() {
    // Get the path of phpmd.
    $phpmd_path = $this->getPhpmd();
    if ($phpmd_path === SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO) {
      $this->registry['phpmd_path'] = $phpmd_path;
      return $phpmd_path;
    }
    // Get the custom code paths.
    $custom_code = $this->getCustomCodePaths();
    if ($custom_code === SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO) {
      $this->registry['custom_code'] = $custom_code;
      return $custom_code;
    }
    // Get options.
    $valid_options = array(
      'minimumpriority' => NULL,
      'suffixes' => '.php,.module,.install,.test,.inc,.profile,.theme',
      'exclude' => '*.features.*,*_default.inc,*.ds.inc,*.strongarm.inc,*.panelizer.inc,*_defaults.inc,*.box.inc,*.context.inc,*displays.inc',
      'strict' => NULL,
      'ruleset' => 'codesize,naming,design,unusedcode',
    );
    $options = $this->getOptions($valid_options, 'phpmd_');
    $option_string = ' ' . $options['ruleset'];
    foreach ($options as $option => $value) {
      if ($option != 'ruleset') {
        $option_string .= " --$option";
        if ($value !== TRUE) {
          $option_string .= "=$value";
        }
      }
    }
    // Supress XML errors which will be handled by try catch instead.
    libxml_use_internal_errors(TRUE);

    foreach ($custom_code as $path) {
      $output = array();
      $exit_code = 0;
      $command = $phpmd_path . ' ' . $path . ' xml' . $option_string;
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
    if (empty($this->registry['phpmd_out'])) {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS;
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN;
  }

  /**
   * Returns the path of the PHPMD executable.
   *
   * Checks for phpmd inside the vendor directory of site_audit or in the
   * global path. Returns AUDIT_CHECK_SCORE_INFO of phpmd is not found.
   *
   * @return String|int
   *   Path of phpmd executable or AUDIT_CHECK_SCORE_INFO if phpmd not found
   */
  public function getPhpmd() {
    // Get the path of phpmd executable.
    if (is_file(SITE_AUDIT_BASE_PATH . '/vendor/bin/phpmd')) {
      return SITE_AUDIT_BASE_PATH . '/vendor/bin/phpmd';
    }
    $global_phpmd = exec('which phpmd 2>/dev/null');
    if (!empty($global_phpmd)) {
      return $global_phpmd;
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO;
  }

  /**
   * Returns an array containing custom code paths or AUDIT_CHECK_SCORE_INFO.
   *
   * @return array|int
   *   An array contaning custom code paths or AUDIT_CHECK_SCORE_INFO if custom
   *   code paths are not found.
   */
  public function getCustomCodePaths() {
    $custom_code = \Drupal::config('site_audit')->get('custom_code');
    if ($custom_code == NULL) {
      $custom_code = drush_get_option('custom_code', '');
      if (empty($custom_code)) {
        return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO;
      }
      $custom_code = explode(',', $custom_code);
    }
    return $custom_code;
  }

  /**
   * Returns the values of the allowed options for phpmd.
   *
   * @param array $options
   *   An array containing the options to be checked and their default values.
   * @param string $option_prefix
   *   Prefix for the options.
   *
   * @return array
   *   An associative array containing the value of the options indexed by
   *   option name.
   */
  public function getOptions(array $options, $option_prefix) {
    $values = array();
    foreach ($options as $option => $default) {
      $value = drush_get_option($option_prefix . $option, $default);
      if ($value !== NULL) {
        $values[$option] = $value;
      }
    }
    return $values;
  }

}
