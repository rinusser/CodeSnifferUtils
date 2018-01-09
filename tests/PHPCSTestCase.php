<?php
declare(strict_types=1);
/**
 * Requires PHP version 7.1+
 * @author Richard Nusser
 * @copyright 2017-2018 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 * @link https://github.com/rinusser/CodeSnifferUtils
 */

namespace RN\CodeSnifferUtils\Tests;

use PHPUnit\Framework\TestCase;
use PHP_CodeSniffer\Config;
use PHP_CodeSniffer\Ruleset;
use PHP_CodeSniffer\Files\File;

/**
 * Base testcase providing common helpers
 */
class PHPCSTestCase extends TestCase
{
  protected static function _isVerbose(): bool
  {
    return (bool)array_intersect(['-v','--verbose','--debug'],$_SERVER['argv']);
  }

  protected static function _isDebug(): bool
  {
    return in_array('--debug',$_SERVER['argv']);
  }


  protected function _fetchXMLCasesInternal(callable $filter): array
  {
    $rv=[];
    foreach(new \DirectoryIterator(static::BASEPATH) as $ti=>$file)
    {
      $filename=$file->getFilename();
      if($file->isDot() || preg_match('/^\..*\.swp$/',$filename) || !$filter($filename))
        continue;
      $rv[$filename]=[$ti,$filename];
    }
    return $rv;
  }

  protected function _loadFile(string $filename): File
  {
    $fullpath=static::BASEPATH.$filename;
    $config=new Config(['.']);
    $ruleset=new Ruleset($config);

    $content=file_get_contents($fullpath);

    $file=new File($filename,$ruleset,$config);
    $file->setContent($content);
    $file->parse();

    return $file;
  }
}
