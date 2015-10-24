# Overview

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

There are two major versions of Site Audit:

* 7.x-1.x - Supports Drupal 7.
* 8.x-2.x - Supports Drupal 8; under heavy development.

# Reports

Site Audit includes a number of comprehensive reports, each consisting of one
or more checks. Site Audit reports include:

* Cache - optimal Drupal caching settings

# Installation

Site Audit is not a module; do not install it in your site root.

Site Audit can be installed using composer.

```bash
composer global require drupal/site_audit
```

Generally, this installs it to `~/.composer/vendor/drupal/site_audit` directory.
For drush to detect it as a command, either copy or symlink the entire Site Audit
project to either your unified or personal Drush
folder in the commands subdirectory, like

```bash
~/.drush/commands
```

then clear Drush's cache:

```bash
drush cc drush
```

Site Audit depends on many third party tools which should be present inside `vendor`
directory inside Site Audit. Run `composer install` inside site_audit directory to install these.

```bash
composer install
```

See http://docs.drush.org/en/master/commands/ to learn more about installing
commands into Drush.

# Usage

```bash
drush help --filter=site_audit
```

## Audit cache

```bash
drush ac
```

## Produce a HTML report

Create a new file or overwrite:

```bash
drush ac --html --detail > ~/Desktop/report.html
```

Continue writing to a file:

```bash
drush abp --html --detail >> ~/Desktop/report.html
```

Run every report with maximum detail, skipping Google insights and adding
Twitter Bootstrap for styling:

```bash
drush aa --html --bootstrap --detail --skip=insights > ~/Desktop/report.html
```

## Skipping reports or checks

For the Audit All command, an individual report can be skipped by name using
the option --skip.

For all commands, individual checks can be skipped by specifying the combination
of the report name and the check name. For example, if you wanted to skip the
System check in the Status report, use the following convention:

```bash
--skip=StatusSystem
```

Multiple skip values can be used, comma separated.

If you want to permanently opt-out of a check, use the $config array in
settings.php with the individual check names in the same format as the skip
option. For example, to permanently opt-out of the PageCompression check in the
Cache report:

```php
$config['site_audit']['opt_out']['CachePageCompression'] = TRUE;
```

## Vendor specific options

Some commands such as the cache audit (ac) have the ability to optionally
produce results that are specific to a particular platform. Currently only
supports Pantheon, but submit a patch if you have another platform that should
have explicit support that will be helpful to other developers.

```bash
drush @pantheon.SITENAME.ENV --vendor=pantheon --detail ac
```

## Custom Code Paths

Codebase report runs some third party tools on custom code the the drupal site.
For this, paths containing custom code should be specified while running site_audit.
There are two options.

Provide a comma separated list of paths (files or directories) in the option
`custom-code`:

```bash
drush audit-codebase --custom-code="modules/custom,modules/features"
```

Or provide an array of custom code paths in `$config` array in `settings.php`:

```php
$config['site_audit']['custom-code'] = array(
  'modules/custom',
  'modules/features',
);
```

# Adding Reports and Checks

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
it may be easiest to create a ```project.site_audit.inc``` file within your
project to consolidate the functionality. You can also define the Reports and
Checks within the actual Drush command, but I'd recommend instead requiring the
code only upon execution, otherwise every other drush command execution will
include all the overhead of loading custom code.

### Custom Reports

In ```hook_drush_command()```, define a command with the following format:

```php
$items['audit_REPORT'] = array(
  // Describe the Report.
  'description' => dt('DESCRIPTION.'),
  // A short alias for the command. Check site_audit.drush.inc to avoid
  // collisions.
  'aliases' => array('aN'),
  // Specify the maximum bootstrap required for your Checks; if interaction with
  // the actual Drupal site is required, use DRUSH_BOOTSTRAP_DRUPAL_FULL.
  'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_FULL,
  // Names of the individual checks, in order of execution.
  'checks' => array(
    // If a Check is defined in its own file, the location can be passed to a
    // require_once within the Report execution.
    array(
     'name' => 'FirstCheck',
     'location' => __DIR__ . '/site_audit/checks/FirstCheck.php',
    );
    // If just the name of the check is defined, the assumption is made that the
    // code has already been loaded.
    'SecondCheck',
    'ThirdCheck',
    'LastCheck',
  ),
  // Options available for all site_audit commands. You can add your own options
  // as well, but make sure to combine the arrays.
  'options' => site_audit_common_options(),
);
```

Define a command callback so the custom Report can be executed individually.

```php
/**
 * Command callback for drush audit_NAME.
 */
function drush_PROJECT_audit_REPORT() {
  require_once __DIR__ . '/PROJECT.site_audit.inc';
  $report = new SiteAuditReportNAME();
  $report->render();
}
```

Finally, include the custom Report to audit_all. The Report name is
case-sensitive. The location will be used for a ```require_once``` statement
when loading the Report class.

```php
/**
 * Implements hook_drush_command_alter().
 */
function security_review_drush_command_alter(&$command) {
  if ($command['command'] == 'audit_all') {
    $command['reports'][] = array(
      'name' => 'Security',
      'location' => __DIR__ . '/PROJECT.site_audit.inc',
    );
  }
}
```

### Custom Checks

Custom Checks should extend ```SiteAuditCheckAbstract```. If including HTML,
be sure to check to see if the HTML option is being used. For example:

```php
if (drush_get_option('html')) {
  $values = $this->registry['semantically_significant_name'];
  if (drush_get_option('html')) {
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

### Adding custom Checks to an existing Report

As Check names are defined in the Report command, just alter the target
command name.

```php
/**
 * Implements hook_drush_command_alter().
 */
function security_review_drush_command_alter(&$command) {
  if ($command['command'] == 'audit_NAME') {
    $command['checks'][] = array(
      'name' => 'CheckName',
      'location' => __DIR__ . '/PROJECT.site_audit.inc',
    );
  }
}
```


# Testing

Site Audit comes with a suite of functional tests testing all the
platform independent functionality of the project.

### Running the tests

* Run `composer update` to install all the dependencies of site-audit which
  includes PHPUnit and drush that is used for testing.
* Export the environment variable `UNISH_DB_URL`
  with correct MySQL username, password and host for PHPUnit to use for testing

```bash
export UNISH_DB_URL="mysql://USERNAME:PASSWORD@HOST"
```

* To run all the tests, execute `./test.sh` from within the site_audit directory.
* To run the tests selectively, execute `./test.sh --filter="*REPORTNAME*" tests` where `REPORTNAME` is the name of a report, such as `Extensions`

### Adding more tests

* All the tests are present inside the `tests` directory.
* Add test for a particular check inside the proper file for the check's report.
* For a tutorial on writing tests for drush commands, refer to 
  http://ninjaducks.in/hacking/writing-tests-for-drush-commands/


# Release notes

Release notes are maintained at https://www.drupal.org/node/2022771/release

The version of Site Audit is found in ```site_audit.info``` and can be
displayed with the command:

```bash
drush site-audit-version
```

The response will be in the form:

```
Site Audit v#.#
```

# Credits

Site Audit is written and maintained by Jon Peck, http://about.me/jonpeck

Site Audit can be found at:

* https://www.drupal.org/project/site_audit
* https://github.com/fluxsauce/site_audit

Thank you to Suzanne Aldrich, Kelly Bell, Aimee Degnan, Joe Miller, Matt Parker,
Ben Sheldon, Shivanshu Agrawal, David Strauss, and everyone else who has given
feedback and suggestions to make this a better project.
