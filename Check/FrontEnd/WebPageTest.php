<?php
/**
 * @file
 * Contains \SiteAudit\Check\FrontEnd\WebPageTest.
 */

/**
 * Class SiteAuditCheckFrontEndWebPageTest.
 */
class SiteAuditCheckFrontEndWebPageTest extends SiteAuditCheckAbstract {

  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('webpagetest.org');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt("Website's performance analysis by webpagetest.org");
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
    return $this->getResultPass();
  }

  /**
   * Return a string with $number space characters if output type is not json.
   *
   * @param int $number
   *   Number of spaces to be added.
   *
   * @return string
   *   string containing $number spaces if output type is not json.
   */
  public function addSpaces($number) {
    if (!drush_get_option('json')) {
      return str_repeat(' ', $number);
    }
    return '';
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultInfo().
   */
  public function getResultInfo() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getResultPass().
   */
  public function getResultPass() {
    $ret_val = '';
    if (drush_get_option('html')) {
      $ret_val .= '<p><b>Full Report:</b> ' . $this->registry['webpagetest']['data']->data->summary . '</p>';
      $ret_val .= '<p><b>Location:</b> ' . $this->registry['webpagetest']['data']->data->location . '</p>';
      $ret_val .= '<p><b>Connectivity:</b> ' . $this->registry['webpagetest']['data']->data->connectivity . '</p>';

      $ret_val .= '<p><b>Scores:</b></p><ul>';
      $ret_val .= '<li> Use persistent connections (keep alive): ' . $this->registry['webpagetest']['scores']->{'score_keep-alive'} . '</li>';
      $ret_val .= '<li> Use gzip compression for transferring compressable responses: ' . $this->registry['webpagetest']['scores']->score_gzip . '</li>';
      $ret_val .= '<li> Leverage browser caching of static assets: ' . $this->registry['webpagetest']['scores']->score_cache . '</li>';
      $ret_val .= '<li> Use a CDN for all static assets: ' . $this->registry['webpagetest']['scores']->score_cdn . '</li>';
      $ret_val .= '<li> Compress Images: ' . ($this->registry['webpagetest']['scores']->score_compress == -1 ? 'N/A' : $this->registry['webpagetest']['scores']->score_compress) . '</li>';
      $ret_val .= '</ul>';

      $ret_val .= '<p><b>Link to Images:</b></p><ul>';
      $ret_val .= '<li> <a href="' . $this->registry['webpagetest']['images']->waterfall . '">Waterfall</a></li>';
      $ret_val .= '<li> <a href="' . $this->registry['webpagetest']['images']->connectionView . '">Connection View</a></li>';
      $ret_val .= '<li> <a href="' . $this->registry['webpagetest']['images']->checklist . '">Checklist</a></li>';
      $ret_val .= '<li> <a href="' . $this->registry['webpagetest']['images']->screenShot . '">Screenshot</a></li>';
      $ret_val .= '</ul>';

      $ret_val .= '<p><b>Link to Detailed Reports:</b></p><ul>';
      $ret_val .= '<li> <a href="' . $this->registry['webpagetest']['pages']->details . '">Details</a></li>';
      $ret_val .= '<li> <a href="' . $this->registry['webpagetest']['pages']->checklist . '">Checklist</a></li>';
      $ret_val .= '<li> <a href="' . $this->registry['webpagetest']['pages']->breakdown . '">Breakdown</a></li>';
      $ret_val .= '<li> <a href="' . $this->registry['webpagetest']['pages']->domains . '">Domains</a></li>';
      $ret_val .= '<li> <a href="' . $this->registry['webpagetest']['pages']->screenShot . '">Screenshot</a></li>';
      $ret_val .= '</ul>';
    }
    else {
      $ret_val .= 'Full Report: ' . $this->registry['webpagetest']['data']->data->summary;
      $ret_val .= PHP_EOL . $this->addSpaces(4) . 'Location: ' . $this->registry['webpagetest']['data']->data->location;
      $ret_val .= PHP_EOL . $this->addSpaces(4) . 'Connectivity: ' . $this->registry['webpagetest']['data']->data->connectivity;

      $ret_val .= PHP_EOL . $this->addSpaces(4) . 'Scores:';
      $ret_val .= PHP_EOL . $this->addSpaces(6) . '* Use persistent connections (keep alive): ' . $this->registry['webpagetest']['scores']->{'score_keep-alive'};
      $ret_val .= PHP_EOL . $this->addSpaces(6) . '* Use gzip compression for transferring compressable responses: ' . $this->registry['webpagetest']['scores']->score_gzip;
      $ret_val .= PHP_EOL . $this->addSpaces(6) . '* Leverage browser caching of static assets: ' . $this->registry['webpagetest']['scores']->score_cache;
      $ret_val .= PHP_EOL . $this->addSpaces(6) . '* Use a CDN for all static assets: ' . $this->registry['webpagetest']['scores']->score_cdn;
      $ret_val .= PHP_EOL . $this->addSpaces(6) . '* Compress Images: ' . ($this->registry['webpagetest']['scores']->score_compress == -1 ? 'N/A' : $this->registry['webpagetest']['scores']->score_compress);

      $ret_val .= PHP_EOL . $this->addSpaces(4) . 'Link to Images:';
      $ret_val .= PHP_EOL . $this->addSpaces(6) . '* Waterfall: ' . $this->registry['webpagetest']['images']->waterfall;
      $ret_val .= PHP_EOL . $this->addSpaces(6) . '* Connection View: ' . $this->registry['webpagetest']['images']->connectionView;
      $ret_val .= PHP_EOL . $this->addSpaces(6) . '* Checklist: ' . $this->registry['webpagetest']['images']->checklist;
      $ret_val .= PHP_EOL . $this->addSpaces(6) . '* Screenshot: ' . $this->registry['webpagetest']['images']->screenShot;

      $ret_val .= PHP_EOL . $this->addSpaces(4) . 'Link to Detailed Reports:';
      $ret_val .= PHP_EOL . $this->addSpaces(6) . '* Details: ' . $this->registry['webpagetest']['pages']->details;
      $ret_val .= PHP_EOL . $this->addSpaces(6) . '* Checklist: ' . $this->registry['webpagetest']['pages']->checklist;
      $ret_val .= PHP_EOL . $this->addSpaces(6) . '* Breakdown: ' . $this->registry['webpagetest']['pages']->breakdown;
      $ret_val .= PHP_EOL . $this->addSpaces(6) . '* Domains: ' . $this->registry['webpagetest']['pages']->domains;
      $ret_val .= PHP_EOL . $this->addSpaces(6) . '* Screenshot: ' . $this->registry['webpagetest']['pages']->screenShot;
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
    $this->registry['errors'] = array();
    $key = drush_get_option('wpt-key');
    $url = drush_get_option('url');
    if ($key == NULL) {
      $this->registry['errors'][] = dt('No API key provided.');
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL;
    }
    if ($url == NULL) {
      $this->registry['errors'][] = dt('No url provided.');
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL;
    }
    // Start the test.
    $wpt_url = 'http://www.webpagetest.org/runtest.php';
    $wpt_url .= '?url=' . $url;
    $wpt_url .= '&k=' . $key;
    $wpt_url .= '&f=json';

    $ch = curl_init($wpt_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    $result = curl_exec($ch);
    $result_json = json_decode($result);
    // Network connection or any other problem.
    if (is_null($result_json)) {
      $this->abort = TRUE;
      $this->registry['errors'] = array();
      $this->registry['errors'][] = dt('www.webpagetest.org did not provide valid json; raw result: @message', array(
        '@message' => $result,
      ));
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL;
    }
    // Failure.
    if ($result_json->statusCode != 200) {
      $this->abort = TRUE;
      $this->registry['errors'] = array($result_json->statusText);
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL;
    }
    $result_url = $result_json->data->jsonUrl;

    // Keep checking for the results in every 5 seconds.
    $tries = 0;
    $found = FALSE;
    while ($tries < 20) {
      sleep(5);
      $tries++;
      $ch = curl_init($result_url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
      $result = curl_exec($ch);
      $result_json = json_decode($result);
      // Network connection or any other problem.
      if (is_null($result_json) || $result_json->statusCode != 200) {
        continue;
      }
      if ($result_json->statusCode == 200) {
        $found = TRUE;
        break;
      }
    }
    if (!$found) {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL;
    }
    $scores = $result_json->data->average->firstView;
    $score = ($scores->{'score_keep-alive'} + $scores->score_gzip + $scores->score_cache + $scores->score_cdn) / 4;
    $this->registry['webpagetest']['data'] = $result_json;
    $this->registry['webpagetest']['images'] = $result_json->data->runs->{'1'}->firstView->images;
    $this->registry['webpagetest']['pages'] = $result_json->data->runs->{'1'}->firstView->pages;
    $this->registry['webpagetest']['scores'] = $scores;
    if ($score > 80) {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS;
    }
    elseif ($score > 60) {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN;
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL;
  }

}
