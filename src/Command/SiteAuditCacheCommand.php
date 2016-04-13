<?php

/**
 * @file
 * Contains \Drupal\site_audit\Command\SiteAuditCacheCommand.
 */

namespace Drupal\site_audit\Command;

use Drupal\Console\Command\ContainerAwareCommand;
use Drupal\Console\Style\DrupalStyle;
use Drupal\site_audit\Report;
use Drupal\site_audit\Reports\Cache;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class SiteAuditCacheCommand.
 *
 * @package Drupal\site_audit
 */
class SiteAuditCacheCommand extends ContainerAwareCommand {
  /**
   * @var Report
   */
  protected $report;

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->report = new Cache();
    $this
      ->setName('audit:cache')
      ->setDescription($this->report->getLabel())
      ->addOption('detail', NULL, InputOption::VALUE_NONE, 'If set, will give detailed output.');
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(ArgvInput $input, DrupalStyle $output) {
    $this->report->toConsole($input, $output);
  }

}
