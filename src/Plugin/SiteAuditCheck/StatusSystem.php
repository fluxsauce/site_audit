<?php
/**
 * @file
 * Contains Drupal\site_audit\Plugin\SiteAuditCheck\StatusSystem
 */

namespace Drupal\site_audit\Plugin\SiteAuditCheck;

use Drupal\site_audit\Plugin\SiteAuditCheckBase;

/**
 * Provides the StatusSystem Check.
 *
 * @SiteAuditCheck(
 *  id = "status_system",
 *  name = @Translation("System Status"),
 *  description = @Translation("Drupal's status report."),
 *  report = "status"
 * )
 */
class StatusSystem extends SiteAuditCheckBase {

  /**
   * {@inheritdoc}.
   */
  public function getResultFail() {
    return $this->getResultPass();
  }

  /**
   * {@inheritdoc}.
   */
  public function getResultInfo() {}

  /**
   * {@inheritdoc}.
   */
  public function getResultPass() {
    $items = array();
    foreach ($this->registry->requirements as $requirement) {
      // Default to REQUIREMENT_INFO if no severity is set.
      if (!isset($requirement['severity'])) {
        $requirement['severity'] = REQUIREMENT_INFO;
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

      $ret_val = [
        '#theme' => 'table',
        '#header' => [
          $this->t('Title'),
          $this->t('Severity'),
          $this->t('Value'),
        ],
        '#rows' => [],
      ];
      foreach ($items as $item) {
        $ret_val['#rows'][] = [
          'attributes' => ['class' => $item['class']],
          'data' => [
             $item['title'],
             $item['severity'],
             $item['value'],
          ],
        ];
      }
    }
    return $ret_val;
  }

  /**
   * {@inheritdoc}.
   */
  public function getResultWarn() {
    return $this->getResultPass();
  }

  /**
   * {@inheritdoc}.
   */
  public function getAction() {}

  /**
   * {@inheritdoc}.
   */
  public function calculateScore() {
    // See system/system.admin.inc function system_status().
    // Load .install files.
    include_once DRUPAL_ROOT . '/core/includes/install.inc';
    drupal_load_updates();

    // Check run-time requirements and status information.
    $this->registry->requirements = \Drupal::moduleHandler()->invokeAll('requirements', array('runtime'));
    usort($this->registry->requirements, function($a, $b) {
      if (!isset($a['weight'])) {
        if (!isset($b['weight'])) {
          return strcmp($a['title'], $b['title']);
        }
        return -$b['weight'];
      }
      return isset($b['weight']) ? $a['weight'] - $b['weight'] : $a['weight'];
    });

    $this->percentOverride = 0;
    $requirements_with_severity = array();
    foreach ($this->registry->requirements as $key => $value) {
      if (isset($value['severity'])) {
        $requirements_with_severity[$key] = $value;
      }
    }
    $score_each = 100 / count($requirements_with_severity);

    $worst_severity = REQUIREMENT_INFO;
    foreach ($this->registry->requirements as $requirement) {
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
      return SiteAuditCheckBase::AUDIT_CHECK_SCORE_PASS;
    }
    elseif ($this->percentOverride > 60) {
      return SiteAuditCheckBase::AUDIT_CHECK_SCORE_WARN;
    }
    return SiteAuditCheckBase::AUDIT_CHECK_SCORE_FAIL;
  }

}