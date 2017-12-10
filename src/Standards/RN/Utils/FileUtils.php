<?php
declare(strict_types=1);
/**
 * requires PHP version 7.0+
 * @author Richard Nusser
 * @copyright 2017 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 * @link https://github.com/rinusser/CodeSnifferUtils
 */

namespace RN\CodeSnifferUtils\Utils;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Util\Tokens;
use PHP_CodeSniffer\Config;

/**
 * Class supplying phpcs file helpers
 */
abstract class FileUtils
{
  public const ROOT_NAMESPACE='\\';

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
    self::_assertTokenIs($tokens[$stack_ptr],T_CONST,'T_CONST');

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

  /**
   * Determines whether a variable is being used in a static property access
   * For example:
   *
   *   SomeClass::$var=4;  //this is variable in a static access
   *   $someObj->$var=4;   //this isn't
   *
   * @param File $file   the phpcs file handle to check in
   * @param int  $offset the variable's token offset to check
   * @return bool true if it's a static property access, false otherwise
   */
  public static function isStaticPropertyAccess(File $file, int $offset): bool
  {
    $tokens=$file->getTokens();
    self::_assertTokenIs($tokens[$offset],T_VARIABLE,'T_VARIABLE');
    $prev=$file->findPrevious(Tokens::$emptyTokens,$offset-1,NULL,true);
    return $prev!==false && $tokens[$prev]['code']===T_DOUBLE_COLON;
  }

  /**
   * Returns a list of a closure's imported variable names, if any
   * For example:
   *
   *   function() {}        //has no imports, results in empty array
   *   function() use ($x)  //imports 1 variable, results in ['$x']
   *
   * @param File $file   the phpcs file handle to check in
   * @param int  $offset the closure's token offset
   * @return array the list of imported variables - empty array if there aren't any
   */
  public static function getClosureImports(File $file, int $offset): array
  {
    $tokens=$file->getTokens();
    self::_assertTokenIs($tokens[$offset],T_CLOSURE,'T_CLOSURE');
    $start=$tokens[$offset]['parenthesis_closer']+1;
    $str=$file->getTokensAsString($start,$tokens[$offset]['scope_opener']-$start);
    if(!preg_match('/use *\((.+)\)/',$str,$matches))
      return [];

    $rv=[];
    foreach(explode(',',$matches[1]) as $parameter)
      $rv[]=trim($parameter);

    return $rv;
  }

  /**
   * Determines a file's declared namespace
   *
   * @param File $file the phpcs file handle
   * @return string the file's namespace - will return self::ROOT_NAMESPACE if none set
   */
  public static function getFileNamespace(File $file): string
  {
    $ns=$file->findNext(T_NAMESPACE,0,NULL,false);
    if($ns===false)
      return self::ROOT_NAMESPACE;

    $ns_end=$file->findNext(T_SEMICOLON,$ns,NULL,false);
    $namespace=trim($file->getTokensAsString($ns+1,$ns_end-$ns-1));
    return $namespace;
  }

  /**
   * Determines the list of namespace imports
   * For example:
   *
   *   use A\B;
   *   use C;
   *   use D\E as F;
   *   use G\{H,I};
   *
   * will result in ['B','C','F','H','I']
   *
   * @param File $file the phpcs file handle
   * @return array the list of imported symbols
   */
  public static function getNamespaceImports(File $file): array
  {
    $rv=[];
    $tokens=$file->getTokens();
    $current=0;
    while(true)
    {
      $current=$file->findNext(T_USE,$current+1,NULL,false);
      if($current===false)
        break;
      if($tokens[$current]['column']!=1)
        continue;
      $end=$file->findNext(T_SEMICOLON,$current+1,NULL,false);
      $use=trim($file->getTokensAsString($current+1,$end-$current-1));
      if(preg_match('/\\\\?{?([^\\\\}]+)}?$/',$use,$matches))
      {
        if(strpos($matches[1],' as ')!==false)
        {
          $parts=explode(' ',$matches[1]);
          $rv[]=array_pop($parts);
        }
        elseif(strpos($matches[1],',')!==false)
          $rv=array_merge($rv,array_map('trim',explode(',',$matches[1])));
        else
          $rv[]=$matches[1];
      }
    }
    return $rv;
  }

  /**
   * Determines the list of caught exceptions in a catch block
   * For example:
   *
   *   try {} catch (AnException $e) {}  //will return ['AnException']
   *   try {} catch (A|B|C\D $e) {}      //will return ['A','B','C\D']
   *
   * @param File $file      the phpcs file to handle
   * @param int  $stack_ptr the "catch" token offset
   * @return array the list of caught exceptions (as strings)
   */
  public static function getCaughtExceptions(File $file, int $stack_ptr): array
  {
    $tokens=$file->getTokens();
    $catch=$tokens[$stack_ptr];
    self::_assertTokenIs($catch,T_CATCH,'T_CATCH');
    $start=$catch['parenthesis_opener']+1;
    $caught=$file->getTokensAsString($start,$catch['parenthesis_closer']-$start);
    $caught=substr($caught,0,strpos($caught,'$'));
    return array_map('trim',explode('|',$caught));
  }

  protected static function _assertTokenIs(array $token, $code, string $type): void
  {
    if($token['code']!==$code)
      throw new \InvalidArgumentException('expected '.$type.', got '.$token['type'].' instead.');
  }
}
