<?php
/**
 * @file
 * Contains \AuditReport.
 */

abstract class AuditReport {
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
    $base_class_name = 'AuditCheck' . substr(get_class($this), 11);
    foreach ($this->getCheckNames() as $name) {
      $class_name = $base_class_name . ucfirst(strtolower($name));
      $check = new $class_name($this->registry);
      // Calculate score.
      if ($check->getScore() != AuditCheck::AUDIT_CHECK_SCORE_INFO) {
        // Mark if there's a major failure.
        if ($check->getScore() == AuditCheck::AUDIT_CHECK_SCORE_FAIL) {
          $this->hasFail = TRUE;
        }
        // Total.
        $this->scoreTotal += $check->getScore();
        // Maximum.
        $this->scoreMax += AuditCheck::AUDIT_CHECK_SCORE_PASS;
      }
      // Combine registry.
      $this->registry = array_merge($this->registry, $check->getRegistry());
      // Store all checks.
      $this->checks[$class_name] = $check;
      // Abort the loop if the check says to bail.
      if ($check->shouldAbort()) {
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
   *
   * @return string
   *   Report using Drush native output functions.
   */
  public function toDrush() {
    if ($this->percent == AuditCheck::AUDIT_CHECK_SCORE_INFO) {
      drush_print(dt('!label: Info', array(
        '!label' => $this->getLabel(),
      )));
    }
    else {
      drush_print(dt('!label: @percent%', array(
        '!label' => $this->getLabel(),
        '@percent' => $this->percent,
      )));
    }
    if ($this->percent == 100) {
      drush_log(dt('  No action required.'), 'success');
    }
    if ($this->percent != 100 || drush_get_context('DRUSH_VERBOSE')) {
      foreach ($this->checks as $check) {
        if ($check->getScore() != AuditCheck::AUDIT_CHECK_SCORE_PASS || drush_get_context('DRUSH_VERBOSE')) {
          if (drush_get_context('DRUSH_VERBOSE')) {
            drush_print(dt('!label: !description', array(
              '!label' => $check->getLabel(),
              '!description' => $check->getDescription(),
            )));
          }
          else {
            drush_print(dt('!label', array(
              '!label' => $check->getLabel(),
            )));
          }
          if ($this->percent == AuditCheck::AUDIT_CHECK_SCORE_INFO) {
            drush_print(dt('  !result', array('!result' => $check->getResult())));
          }
          else {
            drush_log(dt('  !result', array(
              '!result' => $check->getResult(),
            )), $check->getScoreDrushLevel());
          }
          if ($check->getAction()) {
            drush_print(dt('    !action', array('!action' => $check->getAction())));
          }
        }
      }
    }
  }

  /**
   * Render response as HTML; does not include head, body, etc.
   *
   * @return string
   *   Report as rendered HTML.
   */
  public function toHtml() {
    $ret_val = '<h2>' . $this->getLabel();
    if ($this->percent != AuditCheck::AUDIT_CHECK_SCORE_INFO) {
      $ret_val .= ': ' . $this->percent . '%';
    }
    $ret_val .= '</h2>';
    if ($this->percent == 100) {
      $ret_val .= '<p>No action required.</p>';
    }
    if ($this->percent != 100 || drush_get_context('DRUSH_VERBOSE')) {
      foreach ($this->checks as $check) {
        if ($check->getScore() != AuditCheck::AUDIT_CHECK_SCORE_PASS || drush_get_context('DRUSH_VERBOSE') || $this->percent == AuditCheck::AUDIT_CHECK_SCORE_INFO) {
          $ret_val .= '<h3>' . $check->getLabel();
          $ret_val .= ': ';
          $ret_val .= '<span style="color:' . $check->getScoreColor() . '">';
          $ret_val .= $check->getScoreLabel();
          $ret_val .= '</span>';
          $ret_val .= '</h3>';
          if (drush_get_context('DRUSH_VERBOSE')) {
            $ret_val .= '<blockquote>' . $check->getDescription() . '</blockquote>';
          }
          $ret_val .= '<p>';
          $ret_val .= $check->getResult();
          $ret_val .= '</p>';
          if ($check->getAction()) {
            $ret_val .= '<p>';
            $ret_val .= $check->getAction();
            $ret_val .= '</p>';
          }
        }
      }
    }
    $ret_val .= "\n";
    return $ret_val;
  }

  /**
   * Render the report; respects drush options.
   */
  public function render() {
    if (drush_get_option('html')) {
      echo $this->toHtml();
    }
    else {
      $this->toDrush();
    }
  }

  /**
   * Get the label for the report of what is being checked.
   *
   * @return string
   *   Human readable label.
   */
  abstract public function getLabel();

  /**
   * Get the names of all the checks within the report.
   *
   * Abstract instead of using pattern matching so order can be manually
   * specified.
   *
   * @return array
   *   Machine readable names.
   */
  abstract public function getCheckNames();
}
