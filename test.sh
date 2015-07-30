#!/usr/bin/env sh

# This script is a modified form of the script present in devel
# module for running drush command file tests.
#
# The original can be found at
# http://cgit.drupalcode.org/devel/tree/run-tests-drush.sh?id=refs/heads;id2=8.x-1.x
#
# This script will run phpunit-based test classes using Drush's
# test framework.  First, the Drush executable is located, and
# then phpunit is invoked, pointing to Drush's phpunit.xml as
# the configuration.
#
# Any parameters that may be passed to `phpunit` may also be used
# with this script.

if [ ! -f "./vendor/bin/phpunit" ]
then
   echo "phpunit not found; please run 'composer install --dev'"
   exit 1
fi
export UNISH_NO_TIMEOUTS=y
export UNISH_DRUPAL_MAJOR_VERSION=8

# The following line is needed is you use a `drush` that differs from `which drush`
DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )
DRUSH_DIRNAME=$DIR/vendor/drush/drush
export UNISH_DRUSH=$DRUSH_DIRNAME/drush
if [ $# = 0 ] ; then
   $DIR/vendor/bin/phpunit --configuration="$DRUSH_DIRNAME/tests" tests
else
   # Pass along any arguments.
   $DIR/vendor/bin/phpunit --configuration="$DRUSH_DIRNAME/tests" $@
fi
