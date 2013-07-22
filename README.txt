= Overview =

Site Audit is a collection of standardized Drush commands for analyzing a
Drupal site for best practices. Originally designed to provide an actionable
report prior to load testing and launch, each report can be read through drush
or written as HTML to a file.

= Installation =

Copy the entire Site Audit project to either your unified or personal drush
folder in the commands subdirectory, then clear drush's cache:

drush cc drush

= Usage =

drush help --filter=site_audit

= Classes =

* AuditCheck - an individual check; try to make them as atomic as possible.
* AuditReport - a collection of checks, run in sequential order. If a check
  sets the abort property to TRUE, no further checks in the report will be
  executed.

= Credits =

Jon Peck, https://www.getpantheon.com/
