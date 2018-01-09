<?php
declare(strict_types=1);
/**
 * Requires PHP version 7.1+
 * @author Richard Nusser
 * @copyright 2017-2018 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 * @link https://github.com/rinusser/CodeSnifferUtils
 */

namespace RN\CodeSnifferUtils\Tests\Utils;

use PHP_CodeSniffer\Files\File;
use RN\CodeSnifferUtils\Files\FileUtils;
use RN\CodeSnifferUtils\Tests\PHPCSTestCase;

/**
 * Test cases for FileUtils
 */
class FileUtilsTest extends PHPCSTestCase
{
  protected const BASEPATH='/phpcs/tests/files/_all/fileutils/';


  /**
   * Called automatically by PHPUnit, sets up this test class
   */
  public static function setUpBeforeClass()
  {
    require(__DIR__.'/../../vendor/squizlabs/php_codesniffer/autoload.php');
    spl_autoload_register('\PHP_CodeSniffer\Autoload::load');
  }

  private function _runCases(string $filename, $token, array $cases, callable $checker, string $fail_message): void
  {
    $message_prefix=$filename.': ';
    $file=$this->_loadFile($filename);

    $pos=-1;
    foreach($cases as $ti=>$expected)
    {
      $case_prefix=$message_prefix.'case index '.$ti.': ';
      $pos=$file->findNext($token,$pos+1);
      $this->assertNotFalse($pos,$case_prefix.'expected another token');
      $actual=$checker($file,$pos);
      $this->assertEquals($expected,$actual,$case_prefix.$fail_message);
    }
    $this->assertFalse($file->findNext($token,$pos+1),$message_prefix.'didn\'t expect another token');
  }

  /**
   * tests FileUtils::findPreviousOnLineExcept
   */
  public function testFindPreviousOnLineExcept()
  {
    $filename='TokensOnLine.php';
    $cases=['T_VARIABLE',
            'T_WHITESPACE',
            false];
    $this->_runCases($filename,PHPCS_T_SEMICOLON,$cases,function(File $file, int $offset) {
      $x=FileUtils::findPreviousOnLineExcept($file,[T_EQUAL,T_LNUMBER],$offset);
      return $x!==false?$file->getTokens()[$x]['type']:false;
    },'previous token on line');
  }

  /**
   * tests FileUtils::getTokensOnLineAfter
   */
  public function testTokensOnLineAfter()
  {
    $filename='TokensOnLine.php';
    $cases=["=1; //comment \n",
            "\n"];
    $this->_runCases($filename,T_VARIABLE,$cases,function(File $file, int $offset) {
      return implode('',array_column(FileUtils::getTokensOnLineAfter($file,$offset),'content'));
    },'tokens on line after');
  }

  /**
   * tests FileUtils::getConstProperties()
   */
  public function testConstProperties()
  {
    $filename='Const.php';
    //        scope      scope_specified
    $cases=[['public',   true],
            ['private',  true],
            ['public',   false],
            ['protected',true]];
    $cases=array_map(function($x){
      return array_combine(['scope','scope_specified'],$x);
    },$cases);
    $this->_runCases($filename,T_CONST,$cases,[FileUtils::class,'getConstProperties'],'class constant properties');
  }

  /**
   * tests FileUtils::isStaticPropertyAccess()
   */
  public function testStaticPropertyAccess()
  {
    $filename='StaticPropertyAccess.php';
    $cases=[false,false,false,true,false,false,false,false,false,true];
    $this->_runCases($filename,T_VARIABLE,$cases,[FileUtils::class,'isStaticPropertyAccess'],'static property access');
  }

  /**
   * tests FileUtils::getClosureImports()
   */
  public function testClosureImports()
  {
    $filename='Closure.php';
    $cases=[['$b','$c'],
            [],
            ['$d']];
    $this->_runCases($filename,T_CLOSURE,$cases,[FileUtils::class,'getClosureImports'],'closure imports');
  }

  /**
   * tests FileUtils::getFileNamespace()
   */
  public function testFileNamespace()
  {
    $cases=[['Namespace.php','A\B'],
            ['Empty.php',FileUtils::ROOT_NAMESPACE]];
    foreach($cases as [$filename,$case])
      $this->_runCases($filename,T_OPEN_TAG,[$case],[FileUtils::class,'getFileNamespace'],'file namespace');
  }

  /**
   * tests FileUtils::getNamespaceImports()
   */
  public function testNamespaceImports()
  {
    $cases=[['Use.php',['B','D','E','G','K','M','N','X']],
            ['Empty.php',[]]];
    foreach($cases as [$filename,$case])
      $this->_runCases($filename,T_OPEN_TAG,[$case],[FileUtils::class,'getNamespaceImports'],'namespace imports');
  }

  /**
   * tests FileUtils::getCaughtExceptions()
   */
  public function testCaughtExceptions()
  {
    $filename='Exceptions.php';
    $cases=[['Exception'],
            ['InvalidArgumentException','Exception'],
            ['\InvalidArgumentException','Exception'],
            ['\InvalidArgumentException','Exception','A\B\C','D']];
    $this->_runCases($filename,T_CATCH,$cases,[FileUtils::class,'getCaughtExceptions'],'caught exceptions');
  }

  /**
   * tests FileUtils::findReturnType()
   */
  public function testFindReturnType()
  {
    $filename='ReturnType.php';
    $cases=[NULL,
            NULL,
            'int',
            'StdClass',
            '?iterable',
            '?object',
            '\Exception',
            '\DateTime',
            'A\B\C',
            'D\E\F',
            '\G\H\I',
            '\J\K'];
    $this->_runCases($filename,[T_FUNCTION,T_CLOSURE],$cases,[FileUtils::class,'findReturnType'],'return types');
  }
}
