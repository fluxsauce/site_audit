<?php
/**
 * @file
 * Contains \SiteAudit\Check\Codebase\PhpDeadCodeDetection.
 */

use Symfony\Component\Process\Process;

/**
 * Class SiteAuditCheckCodebasePhpDeadCodeDetection.
 */
class SiteAuditCheckCodebasePhpDeadCodeDetection extends SiteAuditCheckAbstract {

  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('PHP Dead Code Detector');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Run phpdcd on custom code.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {
    return dt('Cannot check for unreachable code; an invalid custom code path was specified!');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultInfo().
   */
  public function getResultInfo() {
    if (isset($this->registry['phpdcd_path_error'])) {
      return dt('Missing phpdcd.');
    }
    elseif (isset($this->registry['custom_code'])) {
      return dt('Cannot check for unreachable code; no custom code path specified.');
    }
    return dt('phpdcd does not support XML output yet; please request support in https://github.com/sebastianbergmann/phpdcd/pull/58');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultPass().
   */
  public function getResultPass() {
    return dt('No dead code code found by PHP Dead Code Detector');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultWarn().
   */
  public function getResultWarn() {
    $ret_val = '';
    if (drush_get_option('html') == TRUE) {
      $ret_val .= '<table class="table table-condensed">';
      $ret_val .= '<thead><tr><th>' . dt('Lines Of Code') . '</th><th>' . dt('Starting Line Number') . '</th></tr></thead>';
      foreach ($this->registry['phpdcd_out'] as $filename => $violations) {
        $ret_val .= "<tr align='center'><td colspan='3'>File: $filename</td></tr>";
        foreach ($violations as $violation) {
          $loc = $violation->loc;
          $line = $violation->line;
          $ret_val .= "<tr><td>$loc</td><td>$line</td></tr>";
        }
      }
      $ret_val .= '</table>';
    }
    else {
      $rows = 0;
      foreach ($this->registry['phpdcd_out'] as $filename => $violations) {
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
          $loc = $violation->loc;
          $line = $violation->line;
          $ret_val .= "$loc lines starting from line $line";
        }
      }
    }
    return $ret_val;
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getAction().
   */
  public function getAction() {
    if (isset($this->registry['phpdcd_path_error'])) {
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
    // Get the path of phpdcd.
    $phpdcd_path = $this->getExecPath('phpdcd');
    if ($phpdcd_path === '') {
      $this->registry['phpdcd_path_error'] = TRUE;
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
    $options = $this->getOptions($valid_options, 'phpdcd-');
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
      $command = $phpdcd_path . ' ' . $path . $option_string;
      $process = new Process($command);
      $process->run();
      $error = $process->getErrorOutput();
      if (strpos($error, 'The "--log-xml" option does not exist.') !== FALSE) {
        return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO;
      }
      try {
        $output = simplexml_load_file($temp_file);
        foreach ($output as $data) {
          $filename = $this->getRelativePath((String) $data->file);
          $this->registry['phpdcd_out'][$filename][] = $data;
        }
      }
      catch (Exception $e) {
        $this->logXmlError($path, 'phpdcd');
        continue;
      }
    }
    if (empty($this->registry['phpdcd_out'])) {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS;
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN;
  }

}
