<?php
/**
 * @file
 * Contains \SiteAudit\Check\Codebase\PhpMessDetection.
 */

use Symfony\Component\Process\Process;

/**
 * Class SiteAuditCheckCodebasePhpMessDetection.
 */
class SiteAuditCheckCodebasePhpMessDetection extends SiteAuditCheckAbstract {

  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('PHP Mess Detection');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Check custom code for possible bugs and other suboptimal code.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {
    return dt('Cannot check for suboptimal code; an invalid custom code path was specified!');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultInfo().
   */
  public function getResultInfo() {
    if (isset($this->registry['phpmd_path_error'])) {
      return dt('Missing phpmd.');
    }
    return dt('Cannot check for suboptimal code; no custom code path specified.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultPass().
   */
  public function getResultPass() {
    return dt('Custom Code does not violate any PHP Mess Detector rule.');
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
        $ret_val .= "<tr align='center'><td colspan='3'>File: $filename</td></tr>";
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
    if (isset($this->registry['phpmd_path_error'])) {
      return dt('Run "composer install" from the site_audit installation root to install missing dependencies.');
    }
    if (isset($this->registry['custom_code'])) {
      return dt('Use the --custom-code option.');
    }
    if ($this->getScore() == SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN) {
      return dt('Fix the PHP Mess Detector violations.');
    }
  }

  /**
   * Implements \SiteAudit\Check\Abstract\calculateScore().
   */
  public function calculateScore() {
    // Get the path of phpmd.
    $phpmd_path = $this->getExecPath('phpmd');
    if ($phpmd_path === '') {
      $this->registry['phpmd_path_error'] = TRUE;
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO;
    }
    // Get the custom code paths.
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
      'minimumpriority' => NULL,
      'suffixes' => '.php,.module,.install,.test,.inc,.profile,.theme',
      'exclude' => '*.features.*,*_default.inc,*.ds.inc,*.strongarm.inc,*.panelizer.inc,*_defaults.inc,*.box.inc,*.context.inc,*displays.inc',
      'strict' => NULL,
      'ruleset' => 'codesize,naming,design,unusedcode',
    );
    $options = $this->getOptions($valid_options, 'phpmd-');
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
      $command = $phpmd_path . ' ' . $path . ' xml' . $option_string;
      $process = new Process($command);
      $process->run();
      if ($process->getExitCode() == 1) {
        continue;
      }
      try {
        $output = new SimpleXMLElement($process->getOutput());
        foreach ($output as $file) {
          foreach ($file as $violation) {
            $filename = $this->getRelativePath((String) $file[0]['name']);
            $this->registry['phpmd_out'][$filename][] = $violation;
          }
        }
      }
      catch (Exception $e) {
        $this->logXmlError($path, 'phpmd');
        continue;
      }
    }
    if (empty($this->registry['phpmd_out'])) {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS;
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN;
  }

}
