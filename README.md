# Synopsis

PHP\_CodeSniffer rules implementing personal preferences. Can be used locally or installed as a Docker container. Utilizes other rules shipped with PHP\_CodeSniffer but also contains custom code.

FEATURES.md contains short explanations of custom rules.


# TODOs

* tabs for indenting, plus space for multiline alignment
* single-line function call parameter spacing, e.g. `asdf(1,2,3);`
* references to e.g. InvalidArgumentException in wrong namespace
* add fixer code


# Requirements

### Locally Installed

* PHP 7.1+
* PHP\_Codesniffer (tested with 1.5.2)

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

    alias phpcs='docker run --rm -u "$UID:$(id -g)" -v "$PWD:/app" --name phpcs.$(NOW) -it phpcs --basepath=/app/'


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


# Limitations

This is still a work in progress, limitations are currently listed in the TODOs.


# Tests

The testsuite requires PHPUnit and PHP\_CodeSniffer installed. Start the Docker version with:

    ./phpunit.sh

(alternatively use phpunit directly if installed locally)

PHPUnit invokes phpcs (via `tests/TestRunner.php`) for each case (`tests/cases/*.xml`) and then parsing phpcs's output. Tested files are in `tests/files/`.


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
