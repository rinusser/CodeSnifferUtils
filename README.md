# Synopsis

PHP\_CodeSniffer rules implementing personal preferences. Can be used locally or installed as a Docker container. Utilizes other rules shipped with PHP\_CodeSniffer but also contains custom code.

FEATURES.md contains short explanations of custom rules. The source files in `src` adhere to this standard, check those
files for exhaustive examples.

The sources are hosted on [GitHub](https://github.com/rinusser/CodeSnifferUtils).


# Requirements

### Locally Installed

* PHP 7.1+
* PHP\_Codesniffer (tested with 3.1.1)

Tests additionally require PHPUnit (tested with 6.4, should work with 5.4+).

### Docker

* Docker (tested with 2.0.0)


# Installation

### Locally Installed

Assuming PHP and PHP\_CodeSniffer are installed already (and there is a "phpcs" wrapper available) you can either:

* (preferably) register the additional standards directory with phpcs via `phpcs --config-set installed_paths /path/to/this/src/Standards`, or
* copy src/Standards/\* into your PHP\_CodeSniffer's Standards/ directory.

You might need to increase PHP's memory limit if running phpcs halts with a fatal error ("Allowed memory size...").

### Docker

Build the docker image:

    docker build -t <imagename> .

or use `./build.sh`. It's recommended to create an alias for running the container, e.g. (assuming image name "phpcs"):

    alias phpcs='docker run --rm -u "$UID:$(id -g)" -v "$PWD:/app" --name phpcs.$(NOW) -it \
                   phpcs --basepath=/app/'


# Usage

Regardless of whether you installed the standard locally or use the Docker container, ./phpcs.xml is parsed as usual.

### Locally Installed

This depends on your local PHP\_CodeSniffer installation, usually there's a "phpcs" wrapper installed you can just call from the command line. To manually
change to this coding standard you can just pass it on the command line:

    phpcs --standard=rn

### Docker

Assuming you added the "phpcs" alias you can just invoke it directly:

    phpcs

The "RN" coding standard is already set as default in the docker image. If you haven't added the alias, you'll need to start the container manually, at least
like this:

    docker run --rm -v "$PWD:/app" -it <imagename>

### Referencing from Other Rulesets

A lot of the RN.\* rules require a custom autoloader. The "RN" phpcs standard already includes this autoloader, so if you reference
the entire standard you don't need to do anything special. This applies to both entire phpcs standards (ruleset.xml) and
application's individual setups (phpcs.xml):

    <ruleset name="all">
      <file>.</file>
      <rule ref="RN"/>  <!-- includes autoloader, everything will work just fine -->
    </ruleset>

If you just use individual rules the autoloader isn't included automatically so you'll need to do it yourself:

    <ruleset name="individual">
      <file>.</file>
      <autoload>/path/to/CodeSnifferUtils/src/autoloader.php</autoload>
      <rule ref="RN.Spacing.Use"/>  <!-- this would fail without the autoloader -->
    </ruleset>


# Tests

The testsuite requires PHPUnit and PHP\_CodeSniffer installed. Start the Docker version with:

    ./phpunit.sh

(alternatively use phpunit directly if installed locally)

PHPUnit invokes phpcs (via `tests/TestRunner.php`) for each case (`tests/cases/*.xml`) and then parsing phpcs's output.
Tested files are in `tests/files/`.

If any tested errors are fixable the test runner will run phpcbf on a copy of the test case's files and run phpcs on
the modified copies to confirm the fixable errors were actually fixed.

If you pass the `--debug` argument to phpunit the test runner won't clean up temporary files in /tmp/. Use this to
debug fixer code.

If you pass the `--bash` argument to phpunit.sh it will invoke a shell in the Docker container instead of running
tests. Use this to access temporary files created by the test, e.g.:

    $ ./phpunit.sh --bash
    copy/paste this to execute tests:

      alias ll='ls -la';  /phpcs/vendor/bin/phpunit --color=always

    I have no name!@abcdef012345:/phpcs$ vendor/bin/phpunit --debug --filter assign
    [...]
    OK (1 test, 6 assertions)

After that you'll find the files fixed by phpcbf in the /tmp/CodeSnifferUtils\* directories.


# Legal

## Copyright

Copyright (C) 2017 Richard Nusser

## License

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License, LICENSE.md,
along with this program. If not, see <http://www.gnu.org/licenses/>.
