<?php
/**
 * @file
 * Contains \SiteAudit\Check\Codebase\PhpLOC.
 */

use Symfony\Component\Process\Process;

/**
 * Class SiteAuditCheckCodebasePhpLOC.
 */
class SiteAuditCheckCodebasePhpLOC extends SiteAuditCheckAbstract {

  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('PHP Lines of Code');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Measure the size of custom code.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {
    return dt('Cannot measure the size of the project; an invalid custom code path was specified!');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultInfo().
   */
  public function getResultInfo() {
    if (isset($this->registry['phploc_path_error'])) {
      return dt('Missing phploc.');
    }
    if (isset($this->registry['custom_code'])) {
      return dt('Cannot measure the size of the project; no custom code path specified.');
    }

    $human_readable = array(
      'loc' => 'Lines of Code (LOC)',
      'files' => 'Files',
      'directories' => 'Directories',
      'functions' => 'Functions',
      'namespaces' => 'Namespaces',
      'methods' => 'Methods',
    );

    $ret_val = '';
    if (drush_get_option('html') == TRUE) {
      $ret_val .= '<table class="table table-condensed">';
      $ret_val .= '<thead><tr><th>' . dt('Metric') . '</th><th>' . dt('Value') . '</th></tr></thead>';
      foreach ($this->registry['phploc_out'] as $filename => $metrics) {
        $ret_val .= "<tr align='center'><td colspan='3'><b>File/Directory</b>: $filename</td></tr>";
        foreach ($human_readable as $metric => $name) {
          $e = $metrics->xpath('//' . $metric);
          $ret_val .= "<tr><td>$name</td><td>" . (String) $e[0] . "</td></tr>";
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
        foreach ($human_readable as $metric => $name) {
          $ret_val .= PHP_EOL;
          if (!drush_get_option('json')) {
            $ret_val .= str_repeat(' ', 6);
          }
          $e = $metrics->xpath('//' . $metric);
          $ret_val .= $name . ': ' . (string) $e[0];
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
  public function getResultWarn() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getAction().
   */
  public function getAction() {
    if (isset($this->registry['phploc_path_error'])) {
      return dt('Run "composer install" from the site_audit installation root to install missing dependencies.');
    }
    if (isset($this->registry['custom_code'])) {
      return dt('Use the --custom-code option.');
    }
  }

  /**
   * Implements \SiteAudit\Check\Abstract\calculateScore().
   */
  public function calculateScore() {
    // Get the path of phploc.
    $phploc_path = $this->getExecPath('phploc');
    if ($phploc_path === '') {
      $this->registry['phploc_path_error'] = TRUE;
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO;
    }
    // Get the custom code paths.
    $custom_code = $this->getCustomCodePaths();
    if ($custom_code === FALSE) {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL;
    }
    if (empty($custom_code)) {
      $this->registry['custom_code'] = TRUE;
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO;
    }
    // Get options.
    $valid_options = array(
      'names' => '*.php,*.module,*.install,*.test,*.inc,*.profile,*.theme',
      'names-exclude' => '*.features.*,*_default.inc,*.ds.inc,*.strongarm.inc,*.panelizer.inc,*_defaults.inc,*.box.inc,*.context.inc,*displays.inc',
    );
    $options = $this->getOptions($valid_options, 'phploc-');
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
      $command = $phploc_path . ' ' . $path . $option_string;
      $process = new Process($command);
      $process->run();
      try {
        $output = simplexml_load_file($temp_file);
        $this->registry['phploc_out'][$path] = $output;
      }
      catch (Exception $e) {
        $this->logXmlError($path, 'phploc');
        continue;
      }
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO;
  }

}
