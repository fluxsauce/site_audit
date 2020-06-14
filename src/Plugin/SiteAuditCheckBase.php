<?php

namespace Drupal\site_audit\Plugin;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Base class for Site Audit Check plugins.
 */
abstract class SiteAuditCheckBase extends PluginBase implements SiteAuditCheckInterface {

  use StringTranslationTrait;

  const AUDIT_CHECK_SCORE_INFO = 3;
  const AUDIT_CHECK_SCORE_PASS = 2;
  const AUDIT_CHECK_SCORE_WARN = 1;
  const AUDIT_CHECK_SCORE_FAIL = 0;

  /**
   * Quantifiable number associated with result on a scale of 0 to 2.
   *
   * @var int
   */
  protected $score;

  /**
   * Names of checks that should not run as a result of this check.
   *
   * @var array
   */
  protected $abort = [];

  /**
   * User has opted out of this check in configuration.
   *
   * @var bool
   */
  protected $optOut = FALSE;

  /**
   * If set, will override the Report's percentage.
   *
   * @var int
   */
  protected $percentOverride;

  /**
   * Use for passing data between checks within a report.
   *
   * @var array
   */
  protected $registry;

  /**
   * Are we in a static context.
   *
   * @var bool
   */
  protected $static = TRUE;

  /**
   * Options passed in for reports and checks.
   *
   * @var array
   */
  protected $options = [];

  /**
   * Constructor.
   *
   * @param array $registry
   *   Aggregates data from each individual check.
   * @param bool $opt_out
   *   If set, will not perform checks.
   */
  public function __construct($configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    if (isset($configuration['options'])) {
      $this->options = $configuration['options'];
    }
    $this->registry = $configuration['registry'];
    if (isset($configuration['opt_out']) && !empty($configuration['opt_out'])) {
      $this->optOut = TRUE;
      $this->score = SiteAuditCheckBase::AUDIT_CHECK_SCORE_INFO;
    }
    $static = FALSE;
  }

  /**
   * Determine the result message based on the score.
   *
   * @return string
   *   Human readable message for a given status.
   */
  public function getResult() {
    if ($this->optOut) {
      return t('Opted-out in site configuration.');
    }
    switch ($this->score) {
      case SiteAuditCheckBase::AUDIT_CHECK_SCORE_PASS:
        return $this->getResultPass();

      case SiteAuditCheckBase::AUDIT_CHECK_SCORE_WARN:
        return $this->getResultWarn();

      case SiteAuditCheckBase::AUDIT_CHECK_SCORE_INFO:
        return $this->getResultInfo();

      default:
        return $this->getResultFail();
    }
  }

  /**
   * Get a human readable label for a score.
   *
   * @return string
   *   Pass, Recommendation and so forth.
   */
  public function getScoreLabel() {
    switch ($this->score) {
      case SiteAuditCheckBase::AUDIT_CHECK_SCORE_PASS:
        return $this->t('Pass');

      case SiteAuditCheckBase::AUDIT_CHECK_SCORE_WARN:
        return $this->t('Warning');

      case SiteAuditCheckBase::AUDIT_CHECK_SCORE_INFO:
        return $this->t('Information');

      default:
        return $this->t('Blocker');

    }
  }

  /**
   * Get the ID or machine name for the check.
   *
   * @return string
   *   The ID or machine name for the check.
   */
  public function getId() {
    return $this->getPluginDefinition()['id'];
  }

  /**
   * Get the label for the check that describes, high level what is happening.
   *
   * @return string
   *   Get the label for the check that describes, high level what is happening.
   */
  public function getLabel() {
    return $this->getPluginDefinition()['name'];
  }

  /**
   * Get a more verbose description of what is being checked.
   *
   * @return string
   *   A sentence describing the check; shown in detail mode.
   */
  public function getDescription() {
    return $this->getPluginDefinition()['description'];
  }

  /**
   * Get the description of what happened in a failed check.
   *
   * @return string
   *   Something is explicitly wrong and requires action.
   */
  abstract public function getResultFail();

  /**
   * Get the result of a purely informational check.
   *
   * @return string
   *   Purely informational response.
   */
  abstract public function getResultInfo();

  /**
   * Get a description of what happened in a passed check.
   *
   * @return string
   *   Success; good job.
   */
  abstract public function getResultPass();

  /**
   * Get a description of what happened in a warning check.
   *
   * @return string
   *   Something is wrong, but not horribly so.
   */
  abstract public function getResultWarn();

  /**
   * Get action items for a user to perform if the check did not pass.
   *
   * @return string
   *   Actionable tasks to perform.
   */
  abstract public function getAction();

  /**
   * Display action items for a user to perform.
   *
   * @return string
   *   Actionable tasks to perform, or nothing if check is opted-out.
   */
  public function renderAction() {
    if ($this->optOut) {
      return '';
    }
    return $this->getAction();
  }

  /**
   * Calculate the score.
   *
   * @return int
   *   Constants indicating pass, fail and so forth.
   */
  abstract public function calculateScore();

  /**
   * Get a quantifiable number representing a check result; lazy initialization.
   *
   * @return int
   *   Constants indicating pass, fail and so forth.
   */
  public function getScore() {
    if (!isset($this->score)) {
      $this->score = $this->calculateScore();
    }
    return $this->score;
  }

  /**
   * Get the check registry.
   *
   * @return array
   *   Contains values calculated from this check and any prior checks.
   */
  public function getRegistry() {
    return $this->registry;
  }

  /**
   * Determine whether the check failed so badly that the report must stop.
   *
   * @return bool
   *   Whether to stop the abort after this check.
   */
  public function shouldAbort() {
    return $this->abort;
  }

  /**
   * Get the report percent override, if any.
   *
   * @return int
   *   The overridden percentage.
   */
  public function getPercentOverride() {
    return $this->percentOverride;
  }

  /**
   * Invoke another check's calculateScore() method if it is needed.
   */
  protected function checkInvokeCalculateScore($plugin_id) {
    $checkManager = \Drupal::service('plugin.manager.site_audit_check');
    if (isset($opt_out)) {
      $check = $checkManager->createInstance($plugin_id, ['registry' => $this->registry, 'opt_out' => $opt_out]);
      $check->calculateScore();
    }
  }

}
