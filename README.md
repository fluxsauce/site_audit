# Overview

Site Audit is a collection of standardized Drush commands for analyzing a
site for compliance with Drupal best practices. Originally designed to provide
an actionable report prior to load testing and launch, each report can be read
using Drush, or rendered as either HTML or JSON.

# Installation

Copy the entire Site Audit project to either your unified or personal Drush
folder in the commands subdirectory, like

````
~/.drush/commands
````

then clear Drush's cache:

````
drush cc drush
````

See https://github.com/drush-ops/drush#commands to learn more about installing
commands into Drush.

# Usage

````
drush help --filter=site_audit
````

## Audit cache

````
drush ac
````

## Produce a HTML report

Create a new file or overwrite:

````
drush ac --html --detail > ~/Desktop/report.html
````

Continue writing to a file:

````
drush abp --html --detail >> ~/Desktop/report.html
````

Run every report with maximum detail, skipping Google insights and adding
Twitter Bootstrap for styling:

````
drush aa --html --bootstrap --detail --skip=insights > ~/Desktop/report.html
````

## Skipping reports or checks

For the Audit All command, an individual report can be skipped by name using
the option --skip.

For all commands, individual checks can be skipped by specifying the combination
of the report name and the check name. For example, if you wanted to skip the
System check in the Status report, use the following convention:

````
--skip=StatusSystem
````

Multiple skip values can be used, comma separated.

If you want to permanently opt-out of a check, use the $conf array in
settings.php with the individual check names in the same format as the skip
option. For example, to permanently opt-out of the PageCompression check in the
Cache report:

````
$conf['site_audit']['opt_out']['CachePageCompression'] = TRUE;
````

## Vendor specific options

Some commands such as the cache audit (ac) have the ability to optionally
produce results that are specific to a particular platform. Currently only
supports Pantheon, but submit a patch if you have another platform that should
have explicit support that will be helpful to other developers.

````
drush @pantheon.SITENAME.ENV --vendor=pantheon --detail ac
````

# Adding Reports

There are two classes that you should be aware of:

* SiteAuditReport - a collection of checks, run in sequential order. If a check
  sets the abort property to TRUE, no further checks in the report will be
  executed. Check names in getCheckNames() must be the same as the filename of
  the actual check - including capitalization. Otherwise, you'll get fatal
  errors on case sensitive platforms.

* SiteAuditCheck - an individual check; treat them like a unit test, in that
  each check should be looking for one thing at a time.

The AuditCheck class has a number of properties that are helpful:

* abort - if set to TRUE, will tell the report not to execute any further checks
  after the completion of the current check. For example, if you're checking
  Views but the Views module isn't enabled, abort.
* html - if set to TRUE, indicates that the response contains HTML characters.
* registry - use this to pass content from each check to another. Use sparingly,
  as the registry itself is not cleared. This is safer than a global.

# Credits

Jon Peck, http://about.me/jonpeck
