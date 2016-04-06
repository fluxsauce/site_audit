<?php

/**
 * @file
 * Contains \Drupal\site_audit\Command\SiteAuditCacheCommand.
 */

namespace Drupal\site_audit\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Drupal\Console\Command\Command;
use Drupal\Console\Style\DrupalStyle;
use Drupal\site_audit\Reports\Cache;

/**
 * Class SiteAuditCacheCommand.
 *
 * @package Drupal\site_audit
 */
class SiteAuditCacheCommand extends Command {
  protected $report;

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->report = new Cache();
    $this
      ->setName('site_audit:cache')
      ->setDescription($this->report->getLabel());
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $this->report->toConsole($output);
  }

}
