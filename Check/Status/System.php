<?php
/**
 * @file
 * Contains \SiteAudit\Check\Status\System.
 */

class SiteAuditCheckStatusSystem extends SiteAuditCheckAbstract {
  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('System Status');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt("Drupal's status report.");
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {
    return $this->getResultPass();
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultInfo().
   */
  public function getResultInfo() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getResultPass().
   */
  public function getResultPass() {
    $items = array();
    foreach ($this->registry['requirements'] as $requirement) {
      // Default to REQUIREMENT_INFO if no severity is set.
      if (!isset($requirement['severity'])) {
        $requirement['severity'] = REQUIREMENT_INFO;
      }

      // Reduce verbosity.
      if (!drush_get_option('detail') && $requirement['severity'] < REQUIREMENT_WARNING) {
        continue;
      }

      // Title: severity - value.
      if ($requirement['severity'] == REQUIREMENT_INFO) {
        $class = 'info';
        $severity = 'Info';
      }
      elseif ($requirement['severity'] == REQUIREMENT_OK) {
        $severity = 'Ok';
        $class = 'success';
      }
      elseif ($requirement['severity'] == REQUIREMENT_WARNING) {
        $severity = 'Warning';
        $class = 'warning';
      }
      elseif ($requirement['severity'] == REQUIREMENT_ERROR) {
        $severity = 'Error';
        $class = 'error';
      }

      if (drush_get_option('html')) {
        $value = isset($requirement['value']) && $requirement['value'] ? $requirement['value'] : '&nbsp;';
        $uri = drush_get_context('DRUSH_URI');
        // Unknown URI - strip all links, but leave formatting.
        if ($uri == 'http://default') {
          $value = strip_tags($value, '<em><i><b><strong><span>');
        }
        // Convert relative links to absolute.
        else {
          $value = preg_replace("#(<\s*a\s+[^>]*href\s*=\s*[\"'])(?!http)([^\"'>]+)([\"'>]+)#", '$1' . $uri . '$2$3', $value);
        }

        $item = array(
          'title' => $requirement['title'],
          'severity' => $severity,
          'value' => $value,
          'class' => $class,
        );
      }
      else {
        $item = strip_tags($requirement['title']) . ': ' . $severity;
        if (isset($requirement['value']) && $requirement['value']) {
          $item .= ' - ' . dt('@value', array(
            '@value' => strip_tags($requirement['value']),
          ));
        }
      }
      $items[] = $item;
    }
    if (drush_get_option('html')) {
      $ret_val = '<table class="table table-condensed">';
      $ret_val .= '<thead><tr><th>Title</th><th>Severity</th><th>Value</th></thead>';
      $ret_val .= '<tbody>';
      foreach ($items as $item) {
        $ret_val .= '<tr class="' . $item['class'] . '">';
        $ret_val .= '<td>' . $item['title'] . '</td>';
        $ret_val .= '<td>' . $item['severity'] . '</td>';
        $ret_val .= '<td>' . $item['value'] . '</td>';
        $ret_val .= '</tr>';
      }
      $ret_val .= '</tbody>';
      $ret_val .= '</table>';
    }
    else {
      $separator = PHP_EOL;
      if (!drush_get_option('json')) {
        $separator .= str_repeat(' ', 4);
      }
      $ret_val = implode($separator, $items);
    }
    return $ret_val;
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultWarn().
   */
  public function getResultWarn() {
    return $this->getResultPass();
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getAction().
   */
  public function getAction() {}

  /**
   * Implements \SiteAudit\Check\Abstract\calculateScore().
   */
  public function calculateScore() {
    // See system/system.admin.inc function system_status().
    // Load .install files.
    include_once DRUPAL_ROOT . '/includes/install.inc';
    drupal_load_updates();

    // Check run-time requirements and status information.
    $this->registry['requirements'] = module_invoke_all('requirements', 'runtime');
    usort($this->registry['requirements'], '_system_sort_requirements');

    $this->percentOverride = 0;
    $requirements_with_severity = array();
    foreach ($this->registry['requirements'] as $key => $value) {
      if (isset($value['severity'])) {
        $requirements_with_severity[$key] = $value;
      }
    }
    $score_each = 100 / count($requirements_with_severity);

    $worst_severity = REQUIREMENT_INFO;
    foreach ($this->registry['requirements'] as $requirement) {
      if (isset($requirement['severity'])) {
        if ($requirement['severity'] > $worst_severity) {
          $worst_severity = $requirement['severity'];
        }
        if ($requirement['severity'] == REQUIREMENT_WARNING) {
          $this->percentOverride += $score_each / 2;
        }
        elseif ($requirement['severity'] != REQUIREMENT_ERROR) {
          $this->percentOverride += $score_each;
        }
      }
    }

    $this->percentOverride = round($this->percentOverride);

    if ($this->percentOverride > 80) {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS;
    }
    elseif ($this->percentOverride > 60) {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN;
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL;
  }
}
