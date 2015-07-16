#!/usr/bin/env sh

# This script will run phpunit-based test classes using Drush's
# test framework.  First, the Drush executable is located, and
# then phpunit is invoked, pointing to Drush's phpunit.xml as
# the configuration.
#
# Any parameters that may be passed to `phpunit` may also be used
# with this script.

export UNISH_NO_TIMEOUTS=y
export UNISH_DRUPAL_MAJOR_VERSION=8
DRUSH_PATH="`which drush`"
DRUSH_DIRNAME="`dirname -- "$DRUSH_PATH"`"

# If the path is inside composer
if [[ $DRUSH_DIRNAME == *".composer/vendor/"* ]]
then
    DRUSH_DIRNAME="`dirname -- "$DRUSH_DIRNAME"`"
    DRUSH_DIRNAME+="/drush/drush"
fi

# The following line is needed is you use a `drush` that differs from `which drush`
# export UNISH_DRUSH=$DRUSH_PATH
if [ $# = 0 ] ; then
   phpunit --configuration="$DRUSH_DIRNAME/tests" tests
else
   # Pass along any arguments.
   phpunit --configuration="$DRUSH_DIRNAME/tests" $@
fi