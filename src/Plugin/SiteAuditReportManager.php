<?php

namespace Drupal\site_audit\Plugin;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Provides the Site Audit Report plugin manager.
 */
class SiteAuditReportManager extends DefaultPluginManager {

  /**
   * Constructs a new SiteAuditReportManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/SiteAuditReport', $namespaces, $module_handler, 'Drupal\site_audit\Plugin\SiteAuditReportInterface', 'Drupal\site_audit\Annotation\SiteAuditReport');

    $this->alterInfo('site_audit_site_audit_report_info');
    $this->setCacheBackend($cache_backend, 'site_audit_site_audit_report_plugins');
  }

  /**
   * @inherit
   */
  public function getDefinitions() {
    $definitions = parent::getDefinitions();
    ksort($definitions);
    return $definitions;
  }

}
