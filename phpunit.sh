#!/bin/bash
#run this script to run the test suite

ENTRYPOINT="/phpcs/vendor/bin/phpunit"
ARGS="--color=always"

if [ "$1" == "--bash" ]; then
  shift
  echo copy/paste this to execute tests:
  echo
  echo "  alias ll='ls -la'; " $ENTRYPOINT $ARGS $*
  echo
  ENTRYPOINT="/bin/bash"
  ARGS=""
else
  ARGS="$ARGS $*"
fi

sudo docker run --rm -u "$UID:$(id -g)" --name phpcs-test.$(date +%Y%m%d%H%M%S) -v "$PWD/src:/phpcs/src" -v "$PWD/tests:/phpcs/tests" -it --entrypoint=$ENTRYPOINT -w /phpcs/ phpcs $ARGS
