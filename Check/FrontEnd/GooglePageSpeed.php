<?php
/**
 * @file
 * Contains \SiteAudit\Check\Insights\Analyze.
 */

/**
 * Class SiteAuditCheckFrontEndGooglePageSpeed.
 */
class SiteAuditCheckFrontEndGooglePageSpeed extends SiteAuditCheckAbstract {

  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Google PageSpeed Insights');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Analysis by the Google PageSpeed Insights service');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {
    if (!empty($this->registry['errors'])) {
      if (drush_get_option('html')) {
        $ret_val = '<ul><li>' . implode('</li><li>', $this->registry['errors']) . '</li></ul>';
      }
      else {
        $ret_val = implode(PHP_EOL, $this->registry['errors']);
      }
      return $ret_val;
    }
    $this->getResultPass();
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultInfo().
   */
  public function getResultInfo() {}

  /**
   * Returns the rendered result from JSON decoded object.
   *
   * @param mixed $json_result
   *   Json decodes object.
   *
   * @return string
   *   Rendered Output.
   */
  public function renderResults($json_result) {
    $ret_val = '';
    $stats = array();
    foreach ($json_result->pageStats as $stat_name => $count) {
      $formatted_stat_name = ucfirst(preg_replace('/(?<!^)((?<![[:upper:]])[[:upper:]]|[[:upper:]](?![[:upper:]]))/', ' $1', $stat_name));
      if (stripos($stat_name, 'bytes') !== FALSE) {
        $stats[$formatted_stat_name] = round($count / 1024, 2) . 'kB';
      }
      else {
        $stats[$formatted_stat_name] = $count;
      }
    }

    // Render PageStats.
    if (drush_get_option('html')) {
      $ret_val .= '<h3>' . dt('Page stats') . '</h3>';
      $ret_val .= '<dl class="dl-horizontal">';
      foreach ($stats as $name => $count) {
        $ret_val .= '<dt>' . $name . '</dt>';
        $ret_val .= '<dd>' . $count . '</dd>';
      }
      $ret_val .= '</dl>';
    }
    else {
      $ret_val .= PHP_EOL . str_repeat(' ', 6) . dt('Page stats');
      foreach ($stats as $name => $count) {
        $ret_val .= PHP_EOL;
        if (!drush_get_option('json')) {
          $ret_val .= str_repeat(' ', 8);
        }
        $ret_val .= '* ' . $name . ': ' . $count;
      }
    }

    $impact_filter = drush_get_option('impact');

    // Results.
    if (drush_get_option('html')) {
      $ret_val .= '<h3>' . dt('Detailed results') . '</h3>';
    }
    else {
      $ret_val .= PHP_EOL . str_repeat(' ', 6) . dt('Detailed results:');
    }
    $rendered_result_count = 0;
    // @codingStandardsIgnoreStart
    foreach ($json_result->formattedResults->ruleResults as $resultValues) {
      rtrim($ret_val);
      // Filter out based on impact threshold.
      if ($resultValues->ruleImpact < $impact_filter) {
        continue;
      }
      $rendered_result_count++;

      // Build impact label.
      $impact = '';
      if ($resultValues->ruleImpact >= 3) {
        $impact = dt('(HIGH impact: @ruleImpact)', array(
          '@ruleImpact' => $resultValues->ruleImpact,
        ));
      }
      elseif ($resultValues->ruleImpact > 0) {
        $impact = dt('(low impact: @ruleImpact)', array(
          '@ruleImpact' => $resultValues->ruleImpact,
        ));
      }

      // Render Rule, score and impact.
      $rule_score_impact = dt('@localizedRuleName: @impact', array(
        '@localizedRuleName' => $resultValues->localizedRuleName,
        '@impact' => $impact,
      ));
      if (drush_get_option('html')) {
        $ret_val .= '<div class="alert alert-block ';
        if ($resultValues->ruleImpact == 0) {
          $ret_val .= 'alert-success';
        }
        elseif ($resultValues->ruleImpact >= 10) {
          $ret_val .= 'alert-danger';
        }
        else {
          $ret_val .= 'alert-warning';
        }
        $ret_val .= '">' . $rule_score_impact . '</div>';
      }
      else {
        $ret_val .= PHP_EOL;
        if (!drush_get_option('json')) {
          $ret_val .= str_repeat(' ', 8);
        }
        $ret_val .= $rule_score_impact;
      }

      // Render Summary
      $summary = $resultValues->summary;
      if (!isset($summary->args)) {
        $header = google_json_text_replacement($summary->format);
      }
      else {
        $header = google_json_text_replacement($summary->format, $summary->args);
      }
      if (drush_get_option('html')) {
        $ret_val .= "<p>$header</p>";
      }
      else {
        $ret_val .= PHP_EOL;
        if (!drush_get_option('json')) {
          $ret_val .= str_repeat(' ', 10);
        }
        $ret_val .= $header;
      }

      // URL blocks.
      if (isset($resultValues->urlBlocks)) {
        foreach ($resultValues->urlBlocks as $block) {
          // @codingStandardsIgnoreEnd
          // Header.
          if (!isset($block->header->args)) {
            $header = google_json_text_replacement($block->header->format);
          }
          else {
            $header = google_json_text_replacement($block->header->format, $block->header->args);
          }

          $limit = drush_get_option('limit', 0);
          if ($limit > 0 && isset($block->urls) && ($limit != count($block->urls)) && ($limit < count($block->urls))) {
            $header .= ' ' . dt('Showing @limit out of @total total:', array(
              '@limit' => $limit,
              '@total' => count($block->urls),
            ));
          }

          if (drush_get_option('html')) {
            $ret_val .= '<blockquote>' . $header;
          }
          else {
            $ret_val .= PHP_EOL;
            if (!drush_get_option('json')) {
              $ret_val .= str_repeat(' ', 10);
            }
            $ret_val .= $header;
          }
          if (isset($block->urls) && !empty($block->urls)) {
            $urls = array();

            $count = 0;
            foreach ($block->urls as $url) {
              if ($limit > 0) {
                if (++$count > $limit) {
                  continue;
                }
              }
              $urls[] = google_json_text_replacement($url->result->format, $url->result->args);
            }

            if (drush_get_option('html')) {
              $ret_val .= '<small>' . dt('URLs:');
              $ret_val .= '<ul><li>' . implode('</li><li>', $urls) . '</li></ul>';
              $ret_val .= '</small>';
            }
            else {
              foreach ($urls as $url) {
                $ret_val .= PHP_EOL;
                if (!drush_get_option('json')) {
                  $ret_val .= str_repeat(' ', 12);
                }
                $ret_val .= $url;
              }
            }
          }
          if (drush_get_option('html')) {
            $ret_val .= '</blockquote>';
          }
        }
      }
    }

    // Explain if there are no results so it doesn't look like its broken.
    if ($rendered_result_count == 0) {
      if ($impact_filter) {
        $ret_val .= dt('Nice, no problems to report!');
      }
      else {
        $ret_val .= dt('No results, which is unusual...');
      }
    }
    return $ret_val;
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultPass().
   */
  public function getResultPass() {
    $ret_val = '';
    if (drush_get_option('detail')) {
      if (drush_get_option('html')) {
        $ret_val .= "<h2>" . dt('Desktop') . "</h2>";
      }
      else {
        $ret_val .= dt('Desktop:');
      }
      $ret_val .= $this->renderResults($this->registry['json_desktop_result']);
      if (drush_get_option('html')) {
        $ret_val .= "<h2>" . dt('Mobile') . "</h2>";
      }
      else {
        $ret_val .= PHP_EOL . str_repeat(' ', 4) . dt('Mobile');
      }
      $ret_val .= $this->renderResults($this->registry['json_mobile_result']);
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
  public function getAction() {
    if ($this->score !== SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL) {
      return dt('Full report at https://developers.google.com/speed/pagespeed/insights');
    }
  }

  /**
   * Implements \SiteAudit\Check\Abstract\calculateScore().
   */
  public function calculateScore() {
    $this->registry['errors'] = array();
    $key = drush_get_option('gi-key');
    $url = drush_get_option('url');
    if ($key == NULL) {
      $this->registry['errors'][] = dt('No API key provided.');
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL;
    }
    if ($url == NULL) {
      $this->registry['errors'][] = dt('No url provided.');
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL;
    }

    $pso_url = 'https://www.googleapis.com/pagespeedonline/v2/runPagespeed';
    $pso_url .= '?url=' . $url;
    $pso_url .= '&key=' . $key;

    $ch = curl_init($pso_url . '&strategy=desktop');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    $result = curl_exec($ch);
    $this->registry['json_desktop_result'] = json_decode($result);

    $ch = curl_init($pso_url . '&strategy=mobile');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    $result = curl_exec($ch);
    $this->registry['json_mobile_result'] = json_decode($result);

    // Network connection or any other problem.
    if (is_null($this->registry['json_desktop_result']) || is_null($this->registry['json_mobile_result'])) {
      $this->abort = TRUE;
      $this->registry['errors'][] = dt('www.googleapis.com did not provide valid json; raw result: @message', array(
        '@message' => $result,
      ));
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL;
    }

    // Failure.
    if (isset($this->registry['json_desktop_result']->error)) {
      $this->abort = TRUE;
      $this->registry['errors'] = array();
      foreach ($this->registry['json_desktop_result']->error->errors as $error) {
        $this->registry['errors'][] = dt('@message (@domain - @reason)', array(
          '@message' => $error->message,
          '@domain' => $error->domain,
          '@reason' => $error->reason,
        ));
      }
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL;
    }
    if (isset($this->registry['json_mobile_result']->error)) {
      $this->abort = TRUE;
      $this->registry['errors'] = array();
      foreach ($this->registry['json_mobile_result']->error->errors as $error) {
        $this->registry['errors'][] = dt('@message (@domain - @reason)', array(
          '@message' => $error->message,
          '@domain' => $error->domain,
          '@reason' => $error->reason,
        ));
      }
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL;
    }
    // Access problem.
    if (isset($this->registry['json_desktop_result']->responseCode) && $this->registry['json_desktop_result']->responseCode != 200) {
      $this->abort = TRUE;
      $this->registry['errors'] = array();
      $this->registry['errors'][] = dt('@id is not accessible by PageSpeed Insights - response code (@responsecode)', array(
        '@id' => $this->registry['json_desktop_result']->id,
        '@responsecode' => $this->registry['json_desktop_result']->responseCode,
      ));
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL;
    }
    if (isset($this->registry['json_mobile_result']->responseCode) && $this->registry['json_desktop_result']->responseCode != 200) {
      $this->abort = TRUE;
      $this->registry['errors'] = array();
      $this->registry['errors'][] = dt('@id is not accessible by PageSpeed Insights - response code (@responsecode)', array(
        '@id' => $this->registry['json_desktop_result']->id,
        '@responsecode' => $this->registry['json_desktop_result']->responseCode,
      ));
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL;
    }
    // Overview.
    // Average all scores to get the final score.
    $score = $count = 0;
    foreach ($this->registry['json_mobile_result']->ruleGroups as $group) {
      $score += $group->score;
      $count++;
    }
    foreach ($this->registry['json_desktop_result']->ruleGroups as $group) {
      $score += $group->score;
      $count++;
    }
    $score = intval($score / $count);
    if ($score > 80) {
      $this->percentOverride = $score;
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS;
    }
    elseif ($score > 60) {
      $this->percentOverride = $score;
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN;
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL;
  }

}

/**
 * Perform brute force variable replacement on a Google formatted string.
 *
 * @param string $format
 *   Content to be formatted.
 * @param array $args
 *   Optional; contains standard objects.
 *
 * @return string
 *   Human readable formatted content.
 */
function google_json_text_replacement($format, $args = array()) {
  if (!$args || empty($args)) {
    return $format;
  }
  // If there's a better way of doing this, please let me know.
  $format = preg_replace('/\{\{((BEGIN_LINK)|(END_LINK))\}\}/', '', $format);
  $format_sprintf = preg_replace('/\{\{(.*?)\}\}/', '%s', $format);
  $values = array();
  foreach ($args as $arg) {
    $values[] = $arg->value;
  }
  return vsprintf($format_sprintf, $values);
}
