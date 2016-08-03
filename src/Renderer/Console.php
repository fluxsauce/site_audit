<?php

namespace Drupal\site_audit\Renderer;

use Drupal\Console\Style\DrupalStyle;
use Drupal\site_audit\Renderer;
use Drupal\site_audit\Check;

class Console extends Renderer {
  var $output;

  public function setOutput(DrupalStyle $output) {
    $this->output = $output;
  }

  public function getScoreSymfonyType($score) {
    switch ($score) {
      case Check::AUDIT_CHECK_SCORE_PASS:
        return 'OK';

      case Check::AUDIT_CHECK_SCORE_WARN:
        return 'WARNING';

      case Check::AUDIT_CHECK_SCORE_INFO:
        return 'NOTE';

      default:
        return 'ERROR';

    }
  }

  public function getScoreSymfonyStyle($score) {
    switch ($score) {
      case Check::AUDIT_CHECK_SCORE_PASS:
        return 'fg=black;bg=green';

      case Check::AUDIT_CHECK_SCORE_WARN:
        return 'fg=white;bg=red';

      case Check::AUDIT_CHECK_SCORE_INFO:
        return 'fg=yellow';

      default:
        return 'fg=white;bg=red';

    }
  }

  /**
   * Get the SymfonyStyle method associated with a percentage.
   *
   * @return string
   *   Symfony\Component\Console\Style method.
   */
  public function getSymphonyStyleMethod($percent) {
    if ($percent > 80) {
      return 'success';
    }
    if ($percent > 65) {
      return 'warning';
    }
    if ($percent >= 0) {
      return 'error';
    }
    return 'note';
  }

  public function render($detail = FALSE) {
    $output = $this->output;
    $percent = $this->report->getPercent();

    // Label.
    if (is_null($percent)) {
      $output->info($this->t('!label: Info', array(
        '!label' => $this->report->getLabel(),
      )));
    }
    else {
      $method = $this->getSymphonyStyleMethod($percent);
      $output->$method($this->t('!label: @percent%', array(
        '!label' => $this->report->getLabel(),
        '@percent' => $this->report->getPercent(),
      )));
    }

    // No action required.
    if ($percent == 100) {
      $output->block($this->t('No action required.'), 'OK', 'fg=black;bg=green', str_repeat(' ', 2));
    }

    // Information or a problem.
    if ($detail || $this->report->getPercent() != 100) {
      foreach ($this->report->getChecks() as $check) {
        $score = $check->getScore();
        if (($detail && $score == Check::AUDIT_CHECK_SCORE_INFO) || ($score < Check::AUDIT_CHECK_SCORE_PASS)) {
          // Heading.
          if ($detail) {
            $heading = $this->t('!label: !description', array(
              '!label' => $check->getLabel(),
              '!description' => $check->getDescription(),
            ));
          }
          else {
            $heading = $this->t('!label', array(
              '!label' => $check->getLabel(),
            ));
          }
          $output->block($heading, $this->getScoreSymfonyType($score), $this->getScoreSymfonyStyle($score));

          // Result.
          $result = $check->getResult();
          if (is_array($result)) {
            $output->table($result['headers'], $result['rows']);
          }
          else {
            $output->simple($result);
          }

          // Action.
          $action = $check->renderAction();
          if ($action) {
            $output->info(str_repeat(' ', 2) . $this->t('!action', array(
              '!action' => $check->renderAction(),
            )));
          }
        }
      }
    }
  }
}
