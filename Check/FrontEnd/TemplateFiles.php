<?php
/**
 * @file
 * Contains \SiteAudit\Check\BestPractices\TemplateFiles.
 */

/**
 * Class SiteAuditCheckFrontEndTemplateFiles.
 */
class SiteAuditCheckFrontEndTemplateFiles extends SiteAuditCheckAbstract {

  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Template Files');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Check for business logic in template files of currently active theme.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getResultInfo().
   */
  public function getResultInfo() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getResultPass().
   */
  public function getResultPass() {
    return dt('No business logic found in template files.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultWarn().
   */
  public function getResultWarn() {
    $ret_val = '';
    if (drush_get_option('html') == TRUE) {
      $ret_val .= '<table class="table table-condensed">';
      $ret_val .= '<thead><tr><th>' . dt('Line') . '</th><th>' . dt('Code') . '</th></tr></thead>';
      foreach ($this->registry['template_logic'] as $filename => $violations) {
        $ret_val .= "<tr align='center'><td colspan='3'>File: $filename</td></tr>";
        foreach ($violations as $violation) {
          $ret_val .= '<tr><td>' . $violation[0] . '</td><td>' . htmlspecialchars($violation[1]) . '</td></tr>';
        }
      }
      $ret_val .= '</table>';
    }
    else {
      $rows = 0;
      foreach ($this->registry['template_logic'] as $filename => $violations) {
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
          $ret_val .= 'Line ' . $violation[0] . ' : ' . $violation[1];
        }
      }
    }
    return $ret_val;

  }

  /**
   * Implements \SiteAudit\Check\Abstract\getAction().
   */
  public function getAction() {
    if ($this->score == SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN) {
      return dt('Business logic in template files degrades performance. Move PHP into pre-processors and JavaScript into JavaScript files.');
    }
  }

  /**
   * Implements \SiteAudit\Check\Abstract\calculateScore().
   */
  public function calculateScore() {
    $theme_path = drush_get_context('DRUSH_SELECTED_DRUPAL_ROOT') . '/' . path_to_theme();
    $command = 'find ' . $theme_path . ' -iname "*.tpl.php" ';
    $command .= '-exec grep -n -H "db_select\|db_query\|mysql_query\|drupal_goto\|new .*(\|drupal_set_message\|drupal_get_messages\|cache_clear_all\|function .*(\|exit\|die\|arg(" {} \;';
    $output = array();
    exec($command, $output);
    foreach ($output as $line) {
      $line = explode(':', $line);
      $this->registry['template_logic'][$this->getRelativePath($line[0])][] = array($line[1], $line[2]);
    }
    if (!empty($this->registry['template_logic'])) {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN;
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS;
  }

}
