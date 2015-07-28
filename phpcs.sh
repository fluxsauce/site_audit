#!/usr/bin/env bash
if [ ! -f "./vendor/bin/phpcs" ]
then
  echo "phpcs not found; please run 'composer install --dev'"
  exit 1
fi
./vendor/bin/phpcs \
  --standard=./vendor/drupal/coder/coder_sniffer/Drupal \
  --extensions=php,module,inc,install,test,profile,theme \
  Check \
  Report \
  *.inc
