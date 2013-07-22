= Overview =

Site Audit is a collection of standardized Drush commands for analyzing a
Drupal site for best practices. Originally designed to provide an actionable
report prior to load testing and launch, each report can be read using Drush
or written as HTML to a file.

= Installation =

Copy the entire Site Audit project to either your unified or personal Drush
folder in the commands subdirectory, then clear Drush's cache:

drush cc drush

= Usage =

drush help --filter=site_audit

== Audit cache ==

drush ac

== Produce a HTML report ==

Create a new file or overwrite:

drush ac --html --verbose > ~/desktop/report.html

Continue writing to a file:

drush abp --html --verbose >> ~/desktop/report.html

== Vendor specific options ==

Some commands such as the cache audit (ac) have the ability to optionally
produce results that are specific to a particular platform. Currently only
supports Pantheon, but submit a patch if you have another platform that should
have explicit support that will be helpful to other developers.

= Adding Reports =

There are two classes that you should be aware of:

* AuditReport - a collection of checks, run in sequential order. If a check
  sets the abort property to TRUE, no further checks in the report will be
  executed.

* AuditCheck - an individual check; treat them like a unit test, in that each
  check should be looking for one thing at a time.

The AuditCheck class has a number of properties that are helpful:

* abort - if set to TRUE, will tell the report not to execute any further checks
  after the completion of the current check. For example, if you're checking
  Views but the Views module isn't enabled, abort.
* html - if set to TRUE, indicates that the response contains HTML characters.
* registry - use this to pass content from each check to another. Use sparingly,
  as the registry itself is not cleared. This is safer than a global.

= Credits =

Jon Peck, https://www.getpantheon.com/
