<?php
/**
 * @file
 * Contains \SiteAudit\Check\Abstract.
 */

/**
 * Class SiteAuditCheckAbstract.
 */
abstract class SiteAuditCheckAbstract {
  const AUDIT_CHECK_SCORE_PASS = 2;
  const AUDIT_CHECK_SCORE_WARN = 1;
  const AUDIT_CHECK_SCORE_FAIL = 0;
  const AUDIT_CHECK_SCORE_INFO = -1;

  /**
   * Quantifiable number associated with result on a scale of 0 to 2.
   *
   * @var int
   */
  protected $score;

  /**
   * Indicate that no other checks should be run after this check.
   *
   * @var bool
   */
  protected $abort = FALSE;

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
   * Use for checking whether custom code paths have been validated.
   *
   * @var bool
   */
  private static $customCode = NULL;

  /**
   * Constructor.
   *
   * @param array $registry
   *   Aggregates data from each individual check.
   * @param bool $opt_out
   *   If set, will not perform checks.
   */
  public function __construct(array $registry, $opt_out = FALSE) {
    $this->registry = $registry;
    if ($opt_out) {
      $this->optOut = TRUE;
      $this->score = SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO;
    }
  }

  /**
   * Determine the result message based on the score.
   *
   * @return string
   *   Human readable message for a given status.
   */
  public function getResult() {
    if ($this->optOut) {
      return dt('Opted-out in site configuration.');
    }
    switch ($this->score) {
      case SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS:
        return $this->getResultPass();

      case SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN:
        return $this->getResultWarn();

      case SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO:
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
      case SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS:
        return dt('Pass');

      case SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN:
        return dt('Warning');

      case SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO:
        return dt('Information');

      default:
        return dt('Blocker');

    }
  }

  /**
   * Get the CSS class associated with a score.
   *
   * @return string
   *   Name of the Twitter bootstrap class.
   */
  public function getScoreCssClass() {
    switch ($this->score) {
      case SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS:
        return 'success';

      case SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN:
        return 'warning';

      case SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO:
        return 'info';

      default:
        return 'danger';

    }
  }

  /**
   * Get the Drush message level associated with a score.
   *
   * @return string
   *   Converts the score (integer) to Drush levels.
   */
  public function getScoreDrushLevel() {
    switch ($this->score) {
      case SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS:
        return 'success';

      case SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN:
        return 'warning';

      case SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO:
        return 'notice';

      default:
        return 'error';

    }
  }

  /**
   * Get the label for the check that describes, high level what is happening.
   *
   * @return string
   *   Get the label for the check that describes, high level what is happening.
   */
  abstract public function getLabel();

  /**
   * Get a more verbose description of what is being checked.
   *
   * @return string
   *   A sentence describing the check; shown in detail mode.
   */
  abstract public function getDescription();

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
   * Returns the values of the valid options for a command.
   *
   * This function is used by the codebase report where it needs to get the
   * value of options for different tools it uses. The function takes an
   * array of valid options to check and a prefix for those options.
   *
   * @param array $options
   *   An array containing the options to be checked and their default values.
   * @param string $option_prefix
   *   Prefix for the options.
   *
   * @return array
   *   An associative array containing the value of the options indexed by
   *   option name.
   */
  public function getOptions(array $options, $option_prefix) {
    $values = array();
    foreach ($options as $option => $default) {
      $value = drush_get_option($option_prefix . $option, $default);
      if ($value !== NULL) {
        $values[$option] = $value;
      }
    }
    return $values;
  }

  /**
   * Returns an array containing custom code paths or AUDIT_CHECK_SCORE_INFO.
   *
   * @return array|bool
   *   An array containing custom code paths or an empty array if custom
   *   code paths are not found. If any of the custom code paths is invalid, it
   *   returns FALSE.
   */
  public function getCustomCodePaths() {
    if (SiteAuditCheckAbstract::$customCode === NULL) {
      $custom_code = \Drupal::config('site_audit')->get('custom-code');
      if ($custom_code == NULL) {
        $custom_code = drush_get_option('custom-code', '');
        if (empty($custom_code)) {
          $custom_code = array();
        }
        else {
          $custom_code = explode(',', $custom_code);
        }
      }
      foreach ($custom_code as $path) {
        if (!is_dir($path) && !is_file($path)) {
          drush_log(dt('"@path" given in custom-code option is not a valid file or directory.', array(
            '@path' => $path,
          )));
          return FALSE;
        }
      }
      SiteAuditCheckAbstract::$customCode = $custom_code;
    }
    return SiteAuditCheckAbstract::$customCode;
  }

  /**
   * Returns the path of the executable.
   *
   * Checks for a third-party vendor executable within the site_audit
   * installation. Returns empty string if the executable is not found.
   *
   * @return string
   *   Path of executable or empty string if executable not found
   */
  public function getExecPath($name) {
    // Get the path of executable.
    if (is_file(SITE_AUDIT_BASE_PATH . '/vendor/bin/' . $name)) {
      return SITE_AUDIT_BASE_PATH . '/vendor/bin/' . $name;
    }
    return '';
  }

  /**
   * Logs error if unable to parse XML output.
   *
   * It is used by the checks in codebase reports which parse the xml output
   * generated by third-party tools.
   *
   * @param string $path
   *   Path of the custom code file/directory being checked by the tool.
   * @param string $tool
   *   Name of the tool which generated XML output.
   */
  public function logXmlError($path, $tool) {
    drush_log(dt('Unable to parse XML output for @file generated by @tool', array(
      '@file' => $path,
      '@tool' => $tool,
    )), 'error');
  }

  /**
   * Gives path relative to DRUPAL_ROOT of the path is inside Drupal.
   *
   * @param string $filename
   *   Absolute path of a file or directory.
   *
   * @return string
   *   Path relative to Drupal root path.
   */
  public function getRelativePath($filename) {
    $pos = strpos($filename, DRUPAL_ROOT);
    if ($pos !== FALSE) {
      $filename = substr($filename, $pos + strlen(DRUPAL_ROOT) + 1);
    }
    return $filename;
  }

}
