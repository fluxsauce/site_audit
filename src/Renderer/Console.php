<?php

namespace Drupal\site_audit\Renderer;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\site_audit\Plugin\SiteAuditCheckBase;
use Drupal\site_audit\Renderer;
use Drush\Log\LogLevel;
use Drush\Utils\StringUtils;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Terminal;

/**
 *
 */
class Console extends Renderer {

  public $output;
  public $formatter;

  /**
   *
   */
  public function __construct($report, $logger, $options, $output) {
    parent::__construct($report, $logger, $options, $output);
    $this->output = $output;
    $this->formatter = new FormatterHelper();
  }

  /**
   *
   */
  public function getLogLevel($score) {
    switch ($score) {
      case SiteAuditCheckBase::AUDIT_CHECK_SCORE_PASS:
        return LogLevel::OK;

      case SiteAuditCheckBase::AUDIT_CHECK_SCORE_WARN:
        return LogLevel::WARNING;

      case SiteAuditCheckBase::AUDIT_CHECK_SCORE_INFO:
        return 'NOTE';

      default:
        return 'ERROR';

    }
  }

  /**
   *
   */
  public function getScoreSymfonyStyle($score) {
    switch ($score) {
      case SiteAuditCheckBase::AUDIT_CHECK_SCORE_PASS:
        return 'score-pass';

      case SiteAuditCheckBase::AUDIT_CHECK_SCORE_INFO:
        return 'score-info';

      case SiteAuditCheckBase::AUDIT_CHECK_SCORE_WARN:
      default:
        return 'score-warn';
    }
  }

  /**
   * Get the SymfonyStyle method associated with a percentage.
   *
   * @return string
   *   Symfony\Component\Console\Style method.
   */
  public function getSymphonyStyle($percent) {
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

  /**
   * Take text and center it horizontally in the console.
   */
  public function centerText(string $text) {
    $width = (new Terminal())->getWidth();
    $strlen = $this->formatter->strlenWithoutDecoration($this->output->getFormatter(), $text);
    $spaceCount = ($width - $strlen) / 2;
    for ($i = 0; $i < $spaceCount; $i++) {
      $text = ' ' . $text;
    }
    $this->output->writeln($text);
  }

  /**
   * Create a horizontal rule across the console.
   */
  public function horizontalRule() {
    $width = (new Terminal())->getWidth();
    $line = '';
    for ($i = 0; $i < $width; $i++) {
      $line .= '-';
    }
    $this->output->writeln('<info>' . $line . '</>');
  }

  /**
   * Take Drupal\Core\StringTranslation\TranslatableMarkup and return the string.
   */
  public function interpolate(TranslatableMarkup $message, array $context = []) {
    return StringUtils::interpolate($message, $context);
  }

  /**
   *
   */
  public function render($detail = FALSE) {
    $outputStyle = new OutputFormatterStyle('black', 'white');
    $this->output->getFormatter()->setStyle('report', $outputStyle);

    $outputStyle = new OutputFormatterStyle('black', 'cyan');
    $this->output->getFormatter()->setStyle('check', $outputStyle);

    $outputStyle = new OutputFormatterStyle('cyan', 'black');
    $this->output->getFormatter()->setStyle('action', $outputStyle);

    $outputStyle = new OutputFormatterStyle('green', 'black');
    $this->output->getFormatter()->setStyle('success', $outputStyle);

    $outputStyle = new OutputFormatterStyle('red', 'black');
    $this->output->getFormatter()->setStyle('error', $outputStyle);

    $outputStyle = new OutputFormatterStyle('yellow', 'black');
    $this->output->getFormatter()->setStyle('warning', $outputStyle);

    $outputStyle = new OutputFormatterStyle('cyan', 'black');
    $this->output->getFormatter()->setStyle('note', $outputStyle);

    $outputStyle = new OutputFormatterStyle('black', 'green');
    $this->output->getFormatter()->setStyle('score-pass', $outputStyle);

    $outputStyle = new OutputFormatterStyle('white', 'red');
    $this->output->getFormatter()->setStyle('score-warn', $outputStyle);

    $outputStyle = new OutputFormatterStyle('yellow', 'black');
    $this->output->getFormatter()->setStyle('score-info', $outputStyle);

    $reportText = '';

    $percent = $this->report->getPercent();
    $style = $this->getSymphonyStyle($percent);

    // Add the report header.
    $this->horizontalRule();
    $this->centerText('<info>' . $this->interpolate($this->t('Report: ')) . $this->interpolate($this->report->getLabel()) . '</> - <' . $style . '>' . $percent . '%</>');
    $this->horizontalRule();

    // No action required.
    if ($percent == 100) {
      $this->centerText($this->interpolate($this->t('<success>No action required.</>')));
    }

    // Information or a problem.
    if ($detail || $this->report->getPercent() != 100) {
      foreach ($this->report->getCheckObjects() as $check) {
        $label = $this->report->getLabel() . ' - ' . $check->getLabel();
        $checkText = '';
        $formattedLine = '';
        $score = $check->getScore();

        if (($detail && $score == SiteAuditCheckBase::AUDIT_CHECK_SCORE_INFO) || ($score < SiteAuditCheckBase::AUDIT_CHECK_SCORE_PASS)) {
          // Heading.
          $this->output->writeln($this->formatter->formatSection($label, $check->getDescription()));

          // Result.
          $result = $check->getResult();
          $this->output->writeln($this->formatter->formatSection($label, $this->interpolate($this->t('Result: <@symfony-style>@logLevel</>', ['@logLevel' => ucfirst(strtolower($this->getLogLevel($score))), '@symfony-style' => $this->getScoreSymfonyStyle($score)]))));
          if (is_array($result)) {
            if ($result['#theme'] && method_exists($this, $result['#theme'])) {
              $this->{$result['#theme']}($result, $label);
            }
            else {
              if ($result['headers'] && $result['rows']) {
                // Theme as a table.
                $table = new Table($this->output);
                $table
                  ->setHeaders($result['headers'])
                  ->setRows($result['rows']);
                $this->output->writeln($table->render());
              }
            }
          }
          else {
            $this->output->writeln($this->formatter->formatSection($label, $result));
          }

          // Action.
          $action = $check->renderAction();
          if ($action) {
            if (is_array($action) && $action['#theme'] && method_exists($this, $action['#theme'])) {
              $this->output->writeln($this->formatter->formatSection($label, '<action>' . $this->interpolate($this->t('Action')) . ':</> ' . $action['#title']));
              $this->{$action['#theme']}($action, $label, 'action');
            }
            else {
              $this->output->writeln($this->formatter->formatSection($label, '<action>' . $this->interpolate($this->t('Action')) . ':</> ' . $action));
            }
          }
        }
        $this->output->writeln('');
      }
    }
    $this->output->writeln('<report>' . $reportText . '</>');
  }

  /**
   *
   */
  public function success() {

  }

  /**
   * Theme a table.
   */
  public function table($element, $section = FALSE) {
    if ($section) {
      $this->output->writeln($this->formatter->formatSection($section, $element['#title']));
    }
    // Theme as a table.
    $table = new Table($this->output);
    $table
      ->setHeaders($element['#header'] ?: $element['headers'])
      ->setRows($element['#rows'] ?: $element['rows']);

    $this->output->writeln($table->render());
  }

  /**
   * Theme an item list.
   */
  public function item_list($element, $section = FALSE, $class = 'note') {
    switch ($element['#list_type']) {
      case 'ol':
        $count = 1;
        foreach ($element['#items'] as $item) {
          $text = '<' . $class . '>' . $count . ':</> ' . $item;
          if ($section) {
            $this->output->writeln($this->formatter->formatSection($section, $text));
          }
          else {
            $this->output->writeln($text);
          }
          $count++;
        }
        break;

      case 'ul':
      default:
        foreach ($element['#items'] as $item) {
          $text = '<' . $class . '>*</> ' . $item;
          if ($section) {
            $this->output->writeln($this->formatter->formatSection($section, $text));
          }
          else {
            $this->output->writeln($text);
          }
          $count++;
        }
        break;
    }
  }

}
