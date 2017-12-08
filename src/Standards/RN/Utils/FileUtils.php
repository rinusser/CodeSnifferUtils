<?php
declare(strict_types=1);
/**
 * requires PHP version 7.0+
 * @author Richard Nusser
 * @copyright 2017 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 */

namespace RN\CodeSnifferUtils\Utils;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Config;

/**
 * Class supplying phpcs file helpers
 */
abstract class FileUtils
{
  private static $_phpVersion;

  /**
   * Fetches the effective PHP version analyzed files are being tested for
   *
   * @return int|string the PHP version: 7.1.2 will be returned as 70102
   */
  public static function getTargetPHPVersion()
  {
    if(self::$_phpVersion===NULL)
    {
      $version=Config::getConfigData('php_version');
      if($version===NULL)
        $version=PHP_VERSION_ID;
      self::$_phpVersion=$version;
    }
    return self::$_phpVersion;
  }

  /**
   * Finds tokens on the same line not in list of token types
   *
   * @param File  $file     the phpcsfile handle
   * @param array $excludes the token types to exclude
   * @param int   $start    the reference token to look before of
   * @return int|bool the previous token offset in same line not in $excludes, or false if none found
   */
  public static function findPreviousOnLineExcept(File $file, array $excludes, int $start)
  {
    $tokens=$file->getTokens();
    $start_line=$tokens[$start]['line'];
    $cur=$start-1;
    while($cur>=0 && $tokens[$cur]['line']==$start_line)
    {
      if(!in_array($tokens[$cur]['code'],$excludes))
        return $cur;
      $cur--;
    }
    return false;
  }

  /**
   * Finds and returns tokens on the same line as a given boundary token, excluding that boundary
   *
   * @param File $file     the phpcs file handle
   * @param int  $boundary the token to search after
   * @return array the list of tokens on same line
   */
  public static function getTokensOnLineAfter(File $file, int $boundary): array
  {
    $rv=[];
    $tokens=$file->getTokens();
    $line=$tokens[$boundary]['line'];
    $current=$boundary+1;
    while($tokens[$current]['line']==$line)
      $rv[]=$tokens[$current++];
    return $rv;
  }

  /**
   * Finds a class constant's properties
   *
   * @param File $file      the phpcs file handle
   * @param int  $stack_ptr the const's token offset
   * @return array the constant's properties
   */
  public static function getConstProperties(File $file, int $stack_ptr): array
  {
    $rv['scope']='public';
    $rv['scope_specified']=false;
    $tokens=$file->getTokens();

    while($stack_ptr-->=0)
    {
      switch($tokens[$stack_ptr]['code'])
      {
        case T_PUBLIC:
        case T_PROTECTED:
        case T_PRIVATE:
          $rv['scope']=$tokens[$stack_ptr]['content'];
          $rv['scope_specified']=true;
          break;
        case T_WHITESPACE:
          continue;
        default:
          break 2;
      }
    }

    return $rv;
  }
}
