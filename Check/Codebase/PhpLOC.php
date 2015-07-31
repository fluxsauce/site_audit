<?php
/**
 * @file
 * Contains \SiteAudit\Check\Codebase\PhpLOC.
 */

/**
 * Class SiteAuditCheckCodebasePhpLOC.
 */
class SiteAuditCheckCodebasePhpLOC extends SiteAuditCheckAbstract {
  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('PHP Lines Of Code');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Run phploc on custom code.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getResultInfo().
   */
  public function getResultInfo() {
    if (isset($this->registry['phploc_path'])) {
      return dt('Cannot find phploc in path. Make sure that phploc is present in the PATH or run composer install inside the site_audit directory to install all the dependencies');
    }
    if (isset($this->registry['custom_code'])) {
      return dt('No custom code path specified');
    }
    $ret_val = '';
    if (drush_get_option('html') == TRUE) {
      $ret_val .= '<table class="table table-condensed">';
      $ret_val .= '<thead><tr><th>' . dt('Metric') . '</th><th>' . dt('Value') . '</th></tr></thead>';
      foreach ($this->registry['phploc_out'] as $filename => $metrics) {
        $ret_val .= "<tr align='center'><td colspan='3'><b>File/Directory</b>: $filename</td></tr>";
        foreach ($metrics as $metric) {
          $name = '';
          if (isset($metric['name'])) {
            $name = $metric['name'];
          }
          else {
            $name = $metric->getName();
          }
          $ret_val .= "<tr><td>$name</td><td>$metric</td></tr>";
        }
      }
      $ret_val .= '</table>';
    }
    else {
      $rows = 0;
      foreach ($this->registry['phploc_out'] as $filename => $metrics) {
        if ($rows++ > 0) {
          $ret_val .= PHP_EOL;
          if (!drush_get_option('json')) {
            $ret_val .= str_repeat(' ', 4);
          }
        }
        $ret_val .= dt('File/Directory: @filename', array(
          '@filename' => $filename,
        ));
        foreach ($metrics as $metric) {
          $ret_val .= PHP_EOL;
          if (!drush_get_option('json')) {
            $ret_val .= str_repeat(' ', 6);
          }
          if (isset($metric['name'])) {
            $name = $metric['name'];
          }
          else {
            $name = $metric->getName();
          }
          $ret_val .= "$name : $metric";
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

  }

  /**
   * Implements \SiteAudit\Check\Abstract\getAction().
   */
  public function getAction() {}

  /**
   * Implements \SiteAudit\Check\Abstract\calculateScore().
   */
  public function calculateScore() {
    // Get the path of phploc.
    $phploc_path = $this->getphpLoc();
    if ($phploc_path === SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO) {
      $this->registry['phploc_path'] = $phploc_path;
      return $phploc_path;
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
    $options = $this->getOptions($valid_options, 'phploc_');
    $temp_file = tempnam(sys_get_temp_dir(), 'site_audit');
    $option_string = " --log-xml=$temp_file";
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
      $command = $phploc_path . ' ' . $path . $option_string;
      exec($command, $output, $exit_code);
      try {
        $output = simplexml_load_file($temp_file);
        $this->registry['phploc_out'][$path] = $output;
      }
      catch (Exception $e) {
        continue;
      }
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO;
  }

  /**
   * Returns the path of the phploc executable.
   *
   * Checks for phploc inside the vendor directory of site_audit or in the
   * global path. Returns AUDIT_CHECK_SCORE_INFO of phploc is not found.
   *
   * @return String|int
   *   Path of phploc executable or AUDIT_CHECK_SCORE_INFO if phploc not found
   */
  public function getphpLoc() {
    // Get the path of phploc executable.
    if (is_file(SITE_AUDIT_BASE_PATH . '/vendor/bin/phploc')) {
      return SITE_AUDIT_BASE_PATH . '/vendor/bin/phploc';
    }
    $global_phploc = exec('which phploc 2>/dev/null');
    if (!empty($global_phploc)) {
      return $global_phploc;
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
   * Returns the values of the allowed options for phploc.
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
