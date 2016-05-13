<?php

/**
 * @file
 * Contains \Drupal\site_audit\Command\CacheCommand.
 */

namespace Drupal\site_audit\Command;

use Drupal\Console\Command\ContainerAwareCommand;
use Drupal\Console\Style\DrupalStyle;
use Drupal\site_audit\Renderer;
use Drupal\site_audit\Reports\Cache;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class CacheCommand.
 *
 * @package Drupal\site_audit
 */
class CacheCommand extends ContainerAwareCommand {
  /**
   * @var Cache;
   */
  protected $report;

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->report = new Cache();
    $this
      ->setName('audit:cache')
      ->addOption('json', NULL, InputOption::VALUE_NONE, 'Render as JSON')
      ->addOption('html', NULL, InputOption::VALUE_NONE, 'Render as HTML')
      ->addOption('markdown', NULL, InputOption::VALUE_NONE, 'Render as Markdown')
      ->setDescription($this->report->getLabel());
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(ArgvInput $input, DrupalStyle $output) {
    $detail = $output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE;

    // HTML.
    if ($input->getOption(('html'))) {
      $renderer = new Renderer\Html($this->report);
      $output->writeln($renderer->render($detail));
    }
    // JSON.
    elseif ($input->getOption('json')) {
      $renderer = new Renderer\Json($this->report);
      $output->writeln($renderer->render($detail));
    }
    // Markdown.
    elseif ($input->getOption('markdown')) {
      $renderer = new Renderer\Markdown($this->report);
      $output->writeln($renderer->render($detail));
    }
    // Default to Console.
    else {
      $renderer = new Renderer\Console($this->report);
      $renderer->setOutput($output);
      $renderer->render($detail);
    }
  }

}
