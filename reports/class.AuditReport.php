<?php
/**
 * @file
 * Contains \AuditReport.
 */

abstract class AuditReport {
  /**
   * Public facing label for a group of checks.
   * @var string
   */
  public $label;

  /**
   * Names of the checks that will be included in this report.
   * @var array
   */
  public $checkNames;

  /**
   * Individual check objects.
   * @var array
   */
  protected $checks;

  /**
   * Percentage pass.
   * @var int
   */
  protected $percent;

  /**
   * Maximum score.
   * @var int
   */
  protected $scoreMax = 0;

  /**
   * Total score.
   * @var int
   */
  protected $scoreTotal = 0;

  /**
   * Flag to indicate whether any of the checks are a complete FAIL.
   * @var boolean
   */
  protected $hasFail = FALSE;

  /**
   * Container that's passed between each AuditCheck, better than a global.
   * @var array
   */
  protected $registry = array();

  /**
   * Constructor; loads and executes checks based on the name of this report.
   */
  public function __construct() {
    $this->label = $this->getLabel();
    $this->checkNames = $this->getCheckNames();

    $base_class_name = 'AuditCheck' . substr(get_class($this), 11);
    foreach ($this->checkNames as $name) {
      $class_name = $base_class_name . ucfirst(strtolower($name));
      $check = new $class_name($this->registry);
      // Calculate score.
      if ($check->score != AuditCheck::AUDIT_CHECK_SCORE_INFO) {
        // Mark if there's a major failure.
        if ($check->score == AuditCheck::AUDIT_CHECK_SCORE_FAIL) {
          $this->hasFail = TRUE;
        }
        // Total.
        $this->scoreTotal += $check->score;
        // Maximum.
        $this->scoreMax += AuditCheck::AUDIT_CHECK_SCORE_PASS;
      }
      // Combine registry.
      $this->registry = array_merge($this->registry, $check->registry);
      // Cleanup.
      unset($check->registry);
      // Store all checks.
      $this->checks[$class_name] = $check;
      // Abort the loop if the check says to bail.
      if ($check->abort) {
        break;
      }
    }
    if ($this->scoreMax != 0) {
      $this->percent = round(($this->scoreTotal / $this->scoreMax) * 100);
    }
    else {
      $this->percent = AuditCheck::AUDIT_CHECK_SCORE_INFO;
    }
  }

  /**
   * Render response using Drush.
   */
  public function toDrush() {
    if ($this->percent == AuditCheck::AUDIT_CHECK_SCORE_INFO) {
      drush_print(dt('!label: Info', array(
        '!label' => $this->label,
      )));
    }
    else {
      drush_print(dt('!label: @percent%', array(
        '!label' => $this->label,
        '@percent' => $this->percent,
      )));
    }
    if ($this->percent == 100) {
      drush_log(dt('  No action required.'), 'success');
    }
    if ($this->percent != 100 || drush_get_context('DRUSH_VERBOSE')) {
      foreach ($this->checks as $check) {
        if ($check->score != AuditCheck::AUDIT_CHECK_SCORE_PASS || drush_get_context('DRUSH_VERBOSE')) {
          if (drush_get_context('DRUSH_VERBOSE')) {
            drush_print(dt('!label: !description', array(
              '!label' => $check->label,
              '!description' => $check->description,
            )));
          }
          else {
            drush_print(dt('!label', array(
              '!label' => $check->label,
            )));
          }
          if ($this->percent == AuditCheck::AUDIT_CHECK_SCORE_INFO) {
            drush_print(dt('  !result', array('!result' => $check->result)));
          }
          else {
            drush_log(dt('  !result', array(
              '!result' => $check->result,
            )), $check->getScoreDrushLevel());
          }
          if ($check->action) {
            drush_print(dt('    !action', array('!action' => $check->action)));
          }
        }
      }
    }
  }

  /**
   * Render response as HTML; does not include head, body, etc.
   * @return string
   */
  public function toHtml() {
    $ret_val = '<h2>' . $this->label;
    if ($this->percent != AuditCheck::AUDIT_CHECK_SCORE_INFO) {
      $ret_val .= ': ' . $this->percent . '%';
    }
    $ret_val .= '</h2>';
    if ($this->percent == 100) {
      $ret_val .= '<p>No action required.</p>';
    }
    if ($this->percent != 100 || drush_get_context('DRUSH_VERBOSE')) {
      foreach ($this->checks as $check) {
        if ($check->score != AuditCheck::AUDIT_CHECK_SCORE_PASS || drush_get_context('DRUSH_VERBOSE') || $this->percent == AuditCheck::AUDIT_CHECK_SCORE_INFO) {
          $ret_val .= '<h3>' . $check->label;
          $ret_val .= ': ';
          $ret_val .= '<span style="color:' . $check->getScoreColor() . '">';
          $ret_val .= $check->getScoreLabel();
          $ret_val .= '</span>';
          $ret_val .= '</h3>';
          if (drush_get_context('DRUSH_VERBOSE')) {
            $ret_val .= '<blockquote>' . $check->description . '</blockquote>';
          }
          $ret_val .= '<p>';
          if ($check->html) {
            $ret_val .= $check->result;
          }
          else {
            $ret_val .= htmlspecialchars($check->result);
          }
          $ret_val .= '</p>';
          if ($check->action) {
            $ret_val .= '<p>';
            if ($check->html) {
              $ret_val .= $check->action;
            }
            else {
              $ret_val .= htmlspecialchars($check->action);
            }
            $ret_val .= '</p>';
          }
        }
      }
    }
    $ret_val .= "\n";
    return $ret_val;
  }

  /**
   * Magic get.
   *
   * @param string $name
   * @return mixed
   *    The contents of the guessed property name.
   */
  public function __get($name) {
    // Attempt to return a protected property by name.
    $protected_property_name = '_' . $name;
    if (property_exists($this, $protected_property_name)) {
      return $this->$protected_property_name;
    }

    // Unable to access property; trigger error.
    $trace = debug_backtrace();
    trigger_error(
      'Undefined property via __get(): ' . $name .
      ' in ' . $trace[0]['file'] .
      ' on line ' . $trace[0]['line'],
      E_USER_NOTICE);
    return NULL;
  }

  /**
   * Get the label for the report of what is being checked.
   * @return string
   *   Human readable label.
   */
  abstract public function getLabel();

  /**
   * Get the names of all the checks within the report. Abstract instead of
   * using pattern matching so order can be manually specified.
   * @return array
   *   Machine readable names.
   */
  abstract public function getCheckNames();
}
