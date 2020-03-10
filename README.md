# Site Audit

## Overview

Site Audit is a Drupal static site analysis platform that generates reports with
actionable best practice recommendations.

Every Drupal site is unique, with its own individual configuration, content,
audience, and so forth. With that said, they all have the same core
infrastructure and configuration; Drupal! Therefore, it's possible to perform
performance and behavior gathering to inspect any site.

Site Audit uses a technique known as static program analysis. This mechanism
does not actually perform requests on the target site, and in doing so avoids
the observer effect. It's non-intrusive, so no installation into the target site
or configuration is required.

The end result is a fast, repeatable report that can help detect common problems
and provide introspection into Drupal sites. Reports can be generated in
multiple formats, including plain text, HTML, and JSON.

Site Audit can also be extended by other projects to add custom reports and
checks.

## Reports

Site Audit includes a number of comprehensive reports, each consisting of one
or more checks. Site Audit reports include:

* Cache - optimal Drupal caching settings

## Installation

* Install as you would normally install a contributed Drupal module.
  Visit [Installing Drupal 8 Modules](https://www.drupal.org/node/1897420]) for further information.

## Usage

```bash
drush list --filter=site_audit
```

## Audit Report

```bash
drush site_audit:audit
```

or use the alias:

```bash
drush audit
```

## Produce an HTML report

### Create a new file or overwrite

```bash
drush audit users --format=html > ~Desktop/report.html
```

#### Continue writing to a file

```bash
drush audit security --html --detail >> ~/Desktop/report.html
```

### Run every report with maximum detail, skipping Google insights and adding Twitter Bootstrap for styling

```bash
drush audit --html --bootstrap --detail --skip=insights > ~/Desktop/report.html
```

### Skipping reports or checks

For the Audit command, an individual report can be skipped by name using
the option --skip.

For all commands, individual checks can be skipped by specifying the combination
of the report name and the check name. For example, if you wanted to skip the
System check in the Status report, use the following convention:

```bash
--skip=StatusSystem
```

Multiple skip values can be used, comma separated.

If you want to permanently opt-out of a check, use the $conf array in
settings.php with the individual check names in the same format as the skip
option. For example, to permanently opt-out of the PageCompression check in the
Cache report:

```bash
$conf['site_audit']['opt_out']['CachePageCompression'] = TRUE;
```

## Vendor specific options

Some commands such as the cache audit (ac) have the ability to optionally
produce results that are specific to a particular platform. Currently only
supports Pantheon, but submit a patch if you have another platform that should
have explicit support that will be helpful to other developers.

```bash
drush @pantheon.SITENAME.ENV --vendor=pantheon --detail audit
```

## Adding Reports and Checks

There are two classes that you should be aware of:

* SiteAuditReport - a collection of Checks, run in sequential order. If a check
  sets the abort property to TRUE, no further Checks in the report will be
  executed. The Check names are defined in hook_drush_command with the key name
  "checks". Check names must match the file name of the actual Check, including
  capitalization.

* SiteAuditCheck - an individual Check; treat them like a unit test, in that
  each check should be looking for one thing at a time.

The AuditCheck class has a number of helpful properties:

* abort - if set to TRUE, will tell the report not to execute any further checks
  after the completion of the current check. For example, if you're checking
  Views but the Views module isn't enabled, abort.
* html - if set to TRUE, indicates that the response contains HTML characters.
* registry - use this to pass content from each check to another and within the
  check itself. Use sparingly, as the registry itself is not cleared. This is
  safer than a global.

## Custom Reports and Checks

Site Audit supports specialized Reports or Checks that are specific to a
particular use case or project. A couple steps are needed; regardless of the
approach, a Drush command file is required for the project.

There are no requirements for file structure; depending on the number of checks,
it may be easiest to create a `project.site_audit.inc` file within your
project to consolidate the functionality. You can also define the Reports and
Checks within the actual Drush command, but I'd recommend instead requiring the
code only upon execution, otherwise every other drush command execution will
include all the overhead of loading custom code.

### Custom Reports

Custom Reports should extend `SiteAuditReportBase` and should live in the
`YOURMODULE/src/Plugin/SiteAuditReport/` directory.

The comment for the SiteAuditReportBase class must include the
`@SiteAuditReport` annotation, including _id_, _name_, and _description_.

```php
/**
 * A brief description of your Report.
 *
 * @SiteAuditReport(
 *  id = "new_report",
 *  name = @Translation("A New Report"),
 *  description = @Translation("Reports something")
 * )
 */
```

### Custom Checks

Custom Checks should extend `SiteAuditCheckBase` and should live in the `YOURMODULE/src/Plugin/SiteAuditCheck/` directory.

The comment for the SiteAuditCheckBase class must include the
`@SiteAuditCheck` annotation, including _id_, _name_, _description_, and
_report_. The _report_ should be the ID of the report in which you want to
include your check. In the example above it would be `new_report`.

```php
/**
 * A brief description of your Report.
 *
 * @SiteAuditCheck(
 *  id = "new_check",
 *  name = @Translation("A New Check"),
 *  description = @Translation("Checks something"),
 *  report="new_report"
 * )
 */
```

To include your check to an existing report simply use the ID of that report
for the `report=` parameter.

If including HTML, be sure to check to see if the HTML option is being used.
For example:

```php
if ($this->options['html']) {
  $values = $this->registry['semantically_significant_name'];
  if ($this->options['html']) {
    $ret_val .= '<ul>';
    foreach ($values as $value) {
      $ret_val .= '<li>' . $value . '</li>';
    }
    $ret_val .= '</ul>';
  }
  else {
    // Text-only rendering...
  }
}
```

## Release notes

Release notes are maintained at https://www.drupal.org/node/2022771/release

The version of Site Audit can be displayed with the command:
`composer show drupal/site_audit`

## Credits

Site Audit is written and maintained by Jon Peck, http://about.me/jonpeck

Site Audit can be found at:

* https://www.drupal.org/project/site_audit
* https://github.com/fluxsauce/site_audit

Thank you to Suzanne Aldrich, Kelly Bell, Aimee Degnan, Joe Miller, Matt Parker,
Ben Sheldon, David Strauss, and everyone else who has given feedback and
suggestions to make this a better project.
