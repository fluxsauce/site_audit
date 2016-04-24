<?php
/**
 * @file
 * Contains \SiteAudit\Check\Codebase\PhpCodeSniffer.
 */

use Symfony\Component\Process\Process;

/**
 * Class SiteAuditCheckCodebasePhpCodeSniffer.
 */
class SiteAuditCheckCodebasePhpCodeSniffer extends SiteAuditCheckAbstract {

  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('PHP Code Sniffer');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Check custom code for Drupal coding standards.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {
    return dt('Cannot check for coding standard violations; an invalid custom code path was specified!');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultInfo().
   */
  public function getResultInfo() {
    if (isset($this->registry['phpcs_path_error'])) {
      return dt('Missing phpcs.');
    }
    if (isset($this->registry['custom_code'])) {
      return dt('Cannot check for coding standard violations; no custom code path specified.');
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
        $ret_val .= "<tr align='center'><td colspan='3'>File: $filename Violations: $num_violations</td></tr>";
        foreach ($violations as $violation) {
          $line = $violation['line'];
          $column = $violation['column'];
          $severity = $violation['severity'];
          $message = $violation['message'];
          $ret_val .= "<tr><td>Line $line, Column $column</td><td>$severity</td><td>$message</td></tr>";
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
          $ret_val .= 'Line ' . $violation['line'] . ', Column ' . $violation['column'] . ': ';
          $ret_val .= $violation['severity'] . ' - ' . $violation['message'];
        }
      }
    }
    return $ret_val;

  }

  /**
   * Implements \SiteAudit\Check\Abstract\getAction().
   */
  public function getAction() {
    if (isset($this->registry['phpcs_path_error'])) {
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
    // Get the path of phpcs.
    $phpcs_path = $this->getExecPath('phpcs');
    if ($phpcs_path === '') {
      $this->registry['phpcs_path_error'] = TRUE;
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
      'extensions' => 'php,module,inc,install,test,profile,theme,css,info,txt',
      'ignore' => '*.features.*,*_default.inc,*.ds.inc,*.strongarm.inc,*.panelizer.inc,*_defaults.inc,*.box.inc,*.context.inc,*displays.inc',
      'standard' => SITE_AUDIT_BASE_PATH . '/vendor/drupal/coder/coder_sniffer/Drupal',
    );
    $options = $this->getOptions($valid_options, 'phpcs-');
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
      $command = $phpcs_path . ' ' . $path . $option_string;
      $process = new Process($command);
      $process->run();
      try {
        $output = new SimpleXMLElement($process->getOutput());
        foreach ($output as $file) {
          foreach ($file as $violation) {
            $filename = $this->getRelativePath((String) $file[0]['name']);
            $this->registry['phpcs_out'][$filename][] = $violation;
          }
        }
      }
      catch (Exception $e) {
        $this->logXmlError($path, 'phpcs');
        continue;
      }
    }
    if (empty($this->registry['phpcs_out'])) {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS;
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN;
  }

}
