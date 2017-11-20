#!/bin/bash
#run this script to run the test suite

sudo docker run --rm -u "$UID:$(id -g)" --name phpcs-test.$(date +%Y%m%d%H%M%S) -v "$PWD/src:/phpcs/src" -v "$PWD/tests:/phpcs/tests" -it --entrypoint=/phpcs/vendor/bin/phpunit -w /phpcs/ phpcs --color=always $*
