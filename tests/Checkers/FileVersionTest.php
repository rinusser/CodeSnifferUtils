<?php
declare(strict_types=1);
/**
 * Requires PHP version 7.1+
 * @author Richard Nusser
 * @copyright 2017-2018 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 * @link https://github.com/rinusser/CodeSnifferUtils
 */

namespace RN\CodeSnifferUtils\Tests\Checkers\FileVersion;

use RN\CodeSnifferUtils\Tests\PHPCSTestCase;
use RN\CodeSnifferUtils\Checkers\FileVersionChecker;

/**
 * Test cases for the file version checker
 */
class FileVersionTest extends PHPCSTestCase
{
  protected const BASEPATH='/phpcs/tests/files/_languageversion/';


  /**
   * Called automatically by PHPUnit, sets up this test class
   */
  public static function setUpBeforeClass()
  {
    require(__DIR__.'/../../vendor/squizlabs/php_codesniffer/autoload.php');
    spl_autoload_register('\PHP_CodeSniffer\Autoload::load');
  }

  /**
   * data provider for testFileVersionCases(): finds .php test cases
   *
   * @return array list of .php filenames
   */
  public function fetchFileVersionCases(): array
  {
    return $this->_fetchXMLCasesInternal(function($filename) {
      return substr($filename,0,3)==='php';
    });
  }

  /**
   * tests supplied .php test files against expected PHP version
   *
   * @dataProvider fetchFileVersionCases
   *
   * @param int    $index    the testcase's (non-sequential) index
   * @param string $filename the filename to test
   */
  public function testFileVersion(int $index, string $filename)
  {
    $parts=explode('_',$filename);
    $expected_version=$parts[1];

    $file=$this->_loadFile($filename);
    $this->assertSame($expected_version,FileVersionChecker::findVersion($file),'case '.$index.': '.$filename);
  }
}
