<?php
/**
 * @file
 * Contains \SiteAudit\Check\Block\CacheReport.
 */

class SiteAuditCheckBlockCacheReport extends SiteAuditCheckAbstract {
  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Cache report');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Individual block cache settings.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getResultInfo().
   */
  public function getResultInfo() {
    if (drush_get_option('html')) {
      $ret_val = '<table class="table table-condensed">';
      $ret_val .= '<thead><tr><th>Module</th><th>Block</th><th>State</th></tr></thead>';
      $ret_val .= '<tbody>';
      foreach ($this->registry['blocks'] as $block) {
        $ret_val .= '<tr>';
        $ret_val .= '<td>' . $block['module'] . '</td>';
        $ret_val .= '<td>' . $block['info'] . '</td>';
        $ret_val .= '<td>' . $block['state'] . '</td>';
        $ret_val .= '</tr>';
      }
      $ret_val .= '</tbody>';
      $ret_val .= '</table>';
    }
    else {
      $ret_val  = dt('Module - Info: State') . PHP_EOL;
      if (!drush_get_option('json')) {
        $ret_val .= str_repeat(' ', 4);
      }
      $ret_val .= '--------------------';
      foreach ($this->registry['blocks'] as $block) {
        $ret_val .= PHP_EOL;
        if (!drush_get_option('json')) {
          $ret_val .= str_repeat(' ', 4);
        }
        $ret_val .= "{$block['module']} - {$block['info']}: {$block['state']}";
      }
    }
    return $ret_val;
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultPass().
   */
  public function getResultPass() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getResultWarn().
   */
  public function getResultWarn() {
    return dt('There are no enabled blocks. You should disable the Block module or enable some blocks.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getAction().
   */
  public function getAction() {}

  /**
   * Implements \SiteAudit\Check\Abstract\calculateScore().
   */
  public function calculateScore() {
    $blocks = _block_rehash($this->registry['theme_default']);
    // Only check enabled blocks.
    foreach ($blocks as $bid => $block) {
      if ($block['region'] == -1) {
        unset($blocks[$bid]);
      }
    }
    // Make sure there are blocks to check.
    if (empty($blocks)) {
      $this->abort = TRUE;
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN;
    }

    // Human readable order.
    usort($blocks, function($a, $b) {
      return strcmp($a['module'] . $a['delta'], $b['module'] . $b['delta']);
    });

    $this->registry['blocks'] = array();
    foreach ($blocks as $block) {
      $this->registry['blocks'][] = array(
        'module' => $block['module'],
        'info' => $block['info'],
        'state' => $this->getCacheStateLabel($block['cache']),
      );
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO;
  }

  /**
   * Render a block cache state.
   *
   * @param int $state
   *   Drupal block caching state.
   *
   * @return string
   *   Human readable label.
   */
  public function getCacheStateLabel($state) {
    $label = dt('Unknown');

    switch ($state) {
      case DRUPAL_NO_CACHE:
        $label = dt('None');
        break;

      case DRUPAL_CACHE_CUSTOM:
        $label = dt('Custom');
        break;

      case DRUPAL_CACHE_PER_ROLE:
        $label = dt('Role');
        break;

      case DRUPAL_CACHE_PER_USER:
        $label = dt('User');
        break;

      case DRUPAL_CACHE_PER_PAGE:
        $label = dt('Page');
        break;

      case DRUPAL_CACHE_GLOBAL:
        $label = dt('Global');
        break;
    }
    return $label;
  }
}
