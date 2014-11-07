<?php
/**
 * @file
 * Autoloader.
 */

// Autoload Report classes.
spl_autoload_register(
  function ($class) {
    if (strpos($class, 'SiteAuditReport') === 0) {
      require_once SITE_AUDIT_BASE_PATH . '/Report/' . substr($class, strlen('SiteAuditReport')) . '.php';
    }
  }
);
