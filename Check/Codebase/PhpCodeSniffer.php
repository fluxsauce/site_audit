<?php
/**
 * @file
 * Contains \SiteAudit\Check\Codebase\PhpCodeSniffer.
 */

/**
 * Class SiteAuditCheckCodebasePhpCodeSniffer.
 */
class SiteAuditCheckCodebasePhpCodeSniffer extends SiteAuditCheckAbstract {
  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('PHP Code Sniffer Violations');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Run phpcs on custom code.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getResultInfo().
   */
  public function getResultInfo() {
    if (isset($this->registry['phpcs_path'])) {
      return dt('Cannot find phpcs in path. Make sure that phpcs is present in the PATH or run composer install inside the site_audit directory to install all the dependencies');
    }
    if (isset($this->registry['custom_code'])) {
      return dt('No custom code path specified');
    }
    return dt('Cannot find coding standards inside ' . $this->registry['phpcs_standard']);
  }


  /**
   * Implements \SiteAudit\Check\Abstract\getResultPass().
   */
  public function getResultPass() {
    return dt('No PHP code_sniffer violations.');
  }


  /**
   * Implements \SiteAudit\Check\Abstract\getResultWarn().
   */
  public function getResultWarn() {
    $ret_val = '';
    if (drush_get_option('html') == TRUE) {
      $ret_val .= '<table class="table table-condensed">';
      $ret_val .= '<thead><tr><th>' . dt('Line, Column') . '</th><th>' . dt('Severity') . '</th><th>' . dt('Action') . '</th></tr></thead>';
      foreach ($this->registry['phpcs_out'] as $filename => $violations) {
        $num_violations = count($violations);
        $ret_val .= "<tr align='center'><td colspan='3'><a href='file:///$filename'>File: $filename</a> Violations: $num_violations</td></tr>";
        foreach ($violations as $violation) {
          $line = $violation['line'];
          $column = $violation['column'];
          $severity = $violation['severity'];
          $message = $violation['message'];
          $ret_val .= "<tr><td>$line, $column</td><td>$severity</td><td>$message</td></tr>";
        }
      }
      $ret_val .= '</table>';
    }
    else {
      $rows = 0;
      foreach ($this->registry['phpcs_out'] as $filename => $violations) {
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
          $line = $violation['line'];
          $column = $violation['column'];
          $severity = $violation['severity'];
          $message = $violation['message'];
          $ret_val .= "$line, $column : $severity - $message";
        }
      }
    }
    return $ret_val;

  }

  /**
   * Implements \SiteAudit\Check\Abstract\getAction().
   */
  public function getAction() {}

  /**
   * Implements \SiteAudit\Check\Abstract\calculateScore().
   */
  public function calculateScore() {
    // Get the path of phpcs.
    $phpcs_path = $this->getPhpcs();
    if ($phpcs_path === SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO) {
      $this->registry['phpcs_path'] = $phpcs_path;
      return $phpcs_path;
    }
    // Get the custom code paths.
    $custom_code = $this->getCustomCodePaths();
    if ($custom_code === SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO) {
      $this->registry['custom_code'] = $custom_code;
      return $custom_code;
    }
    // Get options.
    // TODO: put in exhaustive list of ignore patterns.
    $valid_options = array(
      'extensions' => 'php,module,inc,install,test,profile,theme,css,info,txt',
      'ignore' => '*/modules/features/',
      'standard' => SITE_AUDIT_BASE_PATH . '/vendor/drupal/coder/coder_sniffer/Drupal',
    );
    $options = $this->getOptions($valid_options, 'phpcs_');
    // Check if 'standard' is a valid directory.
    if (!is_dir($options['standard'])) {
      $this->registry['phpcs_standard'] = $options['standard'];
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO;
    }
    $temp_file = tempnam(sys_get_temp_dir(), 'site_audit');
    $option_string = " --report=checkstyle";
    foreach ($options as $option => $value) {
      $option_string .= " --$option";
      if ($value !== TRUE) {
        $option_string .= "=$value";
      }
    }
    // Suppress XML errors which will be handled by try catch instead.
    libxml_use_internal_errors(TRUE);

    foreach ($custom_code as $path) {
      $output = array();
      $exit_code = 0;
      $command = $phpcs_path . ' ' . $path . $option_string;
      exec($command, $output, $exit_code);
      try {
        $output = new SimpleXMLElement(implode("\n", $output));
        foreach ($output as $file) {
          foreach ($file as $violation) {
            $this->registry['phpcs_out'][(String) $file[0]['name']][] = $violation;
          }
        }
      }
      catch (Exception $e) {
        continue;
      }
    }
    if (empty($this->registry['phpcs_out'])) {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS;
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN;
  }

  /**
   * Returns the path of the PHPCS executable.
   *
   * Checks for phpcs inside the vendor directory of site_audit or in the
   * global path. Returns AUDIT_CHECK_SCORE_INFO of phpcs is not found.
   *
   * @return string|int
   *   Path of phpcs executable or AUDIT_CHECK_SCORE_INFO if phpcs not found
   */
  public function getPhpcs() {
    // Get the path of phpcs executable.
    if (is_file(SITE_AUDIT_BASE_PATH . '/vendor/bin/phpcs')) {
      return SITE_AUDIT_BASE_PATH . '/vendor/bin/phpcs';
    }
    $global_phpcs = exec('which phpcs 2>/dev/null');
    if (!empty($global_phpcs)) {
      return $global_phpcs;
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
   * Returns the values of the allowed options for phpcs.
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
