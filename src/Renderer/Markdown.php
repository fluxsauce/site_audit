<?php

namespace Drupal\site_audit\Renderer;

use Drupal\site_audit\Renderer;
use Drupal\site_audit\Check;

/**
 *
 */
class Markdown extends Renderer {

  /**
   *
   */
  public function render($detail = FALSE) {
    $ret_val = '## ' . $this->report->getLabel();

    $percent = $this->report->getPercent();

    if ($percent != Check::AUDIT_CHECK_SCORE_INFO) {
      $ret_val .= ': ' . $percent . '%';
    }
    else {
      $ret_val .= ': ' . t('Info');
    }
    $ret_val .= str_repeat(PHP_EOL, 2);

    if ($percent == 100) {
      $ret_val .= '*' . t('Well done!') . '* ';
      $ret_val .= t('No action required.');
      $ret_val .= PHP_EOL;
    }

    if ($detail || $percent != 100) {
      foreach ($this->report->getChecks() as $check) {
        $score = $check->getScore();
        if ($detail || $score < Check::AUDIT_CHECK_SCORE_PASS || $percent == Check::AUDIT_CHECK_SCORE_INFO) {
          // Heading.
          $ret_val .= '### ' . $check->getLabel() . PHP_EOL;
          if ($detail) {
            $ret_val .= '* _' . $check->getDescription() . '_' . PHP_EOL;
          }
          $ret_val .= PHP_EOL;

          // Result.
          $result = $check->getResult();
          // Table.
          if (is_array($result)) {
            $ret_val .= '|' . implode('|', $result['headers']) . '|' . PHP_EOL;
            $ret_val .= str_repeat('|--', count($result['headers'])) . '|' . PHP_EOL;
            foreach ($result['rows'] as $row) {
              $ret_val .= '|`' . implode('`|`', $row) . '`|' . PHP_EOL;
            }
            $ret_val .= PHP_EOL;
          }
          else {
            $ret_val .= $result;
          }

          // Action.
          if ($check->renderAction()) {
            $ret_val .= str_repeat(PHP_EOL, 2);
            $ret_val .= $check->renderAction();
          }
          $ret_val .= PHP_EOL;
        }
      }
    }
    $ret_val .= "\n";
    return $ret_val;
  }

}
