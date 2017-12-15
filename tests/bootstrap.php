<?php
declare(strict_types=1);
/**
 * Bootstrap file for tests: loads required classes before autoloading kicks in.
 *
 * requires PHP version 7.1+
 * @author Richard Nusser
 * @copyright 2017 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 * @link https://github.com/rinusser/CodeSnifferUtils
 */

require_once(__DIR__.'/../src/autoloader.php');
require_once('PHPCSTestCase.php');
require_once('XMLTestCase.php');
