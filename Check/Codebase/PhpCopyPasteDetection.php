<?php
/**
 * @file
 * Contains \SiteAudit\Check\Codebase\PhpMessDetection.
 */

/**
 * Class SiteAuditCheckCodebasePhpCopyPasteDetection.
 */
class SiteAuditCheckCodebasePhpCopyPasteDetection extends SiteAuditCheckAbstract {
  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('PHP Copy/Paste Detector Violations');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Run phpcpd on custom code.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getResultInfo().
   */
  public function getResultInfo() {
    if (isset($this->registry['phpcpd_path'])) {
      return dt('Cannot find phpcpd in path. Make sure that phpcpd is present in the PATH or run composer install inside the site_audit directory to install all the dependencies');
    }
    return dt('No custom code path specified');
  }


  /**
   * Implements \SiteAudit\Check\Abstract\getResultPass().
   */
  public function getResultPass() {
    return dt('No copy-pasted code found by PHP Copy/Paste Detector');
  }


  /**
   * Implements \SiteAudit\Check\Abstract\getResultWarn().
   */
  public function getResultWarn() {
    $ret_val = '';
    if (drush_get_option('html') == TRUE) {
      $ret_val .= '<table class="table table-condensed">';
      $ret_val .= '<thead><tr><th>' . dt('File: Lines') . '</th></thead>';
      foreach ($this->registry['phpcpd_out'] as $duplication) {
        $lines = (int) $duplication['lines'];
        $ret_val .= '<tr><td>';
        foreach ($duplication->file as $file) {
          $path = $file['path'];
          $line_start = (int) $file['line'];
          $line_end = $line_start + $lines;
          $ret_val .= "<a href='file:///$path'>$path</a>: $line_start-$line_end<br>";
        }
        $ret_val .= '</tr></td>';
      }
      $ret_val .= '</table>';
    }
    else {
      $ret_val .= 'Duplications: ' . count($this->registry['phpcpd_out']);
      foreach ($this->registry['phpcpd_out'] as $duplication) {
        $rows = 0;
        $lines = (int) $duplication['lines'];
        foreach ($duplication->file as $file) {
          $ret_val .= PHP_EOL;
          if ($rows++ == 0) {
            $ret_val .= ' -  ';
          }
          else {
            if (!drush_get_option('json')) {
              $ret_val .= str_repeat(' ', 4);
            }
          }
          $path = $file['path'];
          $line_start = (int) $file['line'];
          $line_end = $line_start + $lines;
          $ret_val .= "$path: $line_start-$line_end";
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
    // Get the path of phpcpd.
    $phpcpd_path = $this->getPhpcpd();
    if ($phpcpd_path === SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO) {
      $this->registry['phpcpd_path'] = $phpcpd_path;
      return $phpcpd_path;
    }
    // Get the custom code paths.
    $custom_code = $this->getCustomCodePaths();
    if ($custom_code === SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO) {
      $this->registry['custom_code'] = $custom_code;
      return $custom_code;
    }
    // Get options.
    $valid_options = array(
      'names' => '*.php,*.module,*.install,*.test,*.inc,*.profile,*.theme',
      'names-exclude' => '*.features.*,*_default.inc,*.ds.inc,*.strongarm.inc,*.panelizer.inc,*_defaults.inc,*.box.inc,*.context.inc,*displays.inc',
    );
    $options = $this->getOptions($valid_options, 'phpcpd_');
    $temp_file = tempnam(sys_get_temp_dir(), 'site_audit');
    $option_string = " --log-pmd=$temp_file";
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
      $command = $phpcpd_path . ' ' . $path . $option_string;
      exec($command, $output, $exit_code);
      try {
        $output = simplexml_load_file($temp_file);
        foreach ($output as $duplication) {
          $this->registry['phpcpd_out'][] = $duplication;
        }
      }
      catch (Exception $e) {
        continue;
      }
    }
    if (empty($this->registry['phpcpd_out'])) {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS;
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN;
  }

  /**
   * Returns the path of the PHPCPD executable.
   *
   * Checks for phpcpd inside the vendor directory of site_audit or in the
   * global path. Returns AUDIT_CHECK_SCORE_INFO of phpcpd is not found.
   *
   * @return String|int
   *   Path of phpcpd executable or AUDIT_CHECK_SCORE_INFO if phpcpd not found
   */
  public function getPhpcpd() {
    // Get the path of phpcpd executable.
    if (is_file(SITE_AUDIT_BASE_PATH . '/vendor/bin/phpcpd')) {
      return SITE_AUDIT_BASE_PATH . '/vendor/bin/phpcpd';
    }
    $global_phpcpd = exec('which phpcpd 2>/dev/null');
    if (!empty($global_phpcpd)) {
      return $global_phpcpd;
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
   * Returns the values of the allowed options for phpcpd.
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
