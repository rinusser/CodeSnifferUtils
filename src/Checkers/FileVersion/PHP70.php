<?php
declare(strict_types=1);
/**
 * requires PHP version 7.1+
 * @author Richard Nusser
 * @copyright 2017 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 * @link https://github.com/rinusser/CodeSnifferUtils
 */

namespace RN\CodeSnifferUtils\Checkers\FileVersion;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Util\Tokens;
use RN\CodeSnifferUtils\Files\FileUtils;

/**
 * File version checks for PHP 7.0
 */
class PHP70 extends AbstractVersion
{
  /**
   * Gets called automatically to determine what PHP version we're checking in this class
   *
   * @return string the PHP version
   */
  public function getVersion(): string
  {
    return '7.0';
  }

  /**
   * Checks whether the passed file uses PHP 7.0's strict_types directive
   * For example:
   *
   *   <?php
   *   declare(strict_types=1);
   *
   * @param File $file the phpcs file handle to check
   * @return bool true if any uses were found
   */
  public function usesDeclareStrict(File $file): bool
  {
    $tokens=$file->getTokens();
    foreach(FileUtils::findAllByTypes($file,[T_DECLARE]) as $current)
    {
      $directive=$file->findNext(T_STRING,$tokens[$current]['parenthesis_opener'],$tokens[$current]['parenthesis_closer']);
      if($directive!==false && strtolower($tokens[$directive]['content'])==='strict_types')
        return true;
    }
    return false;
  }

  /**
   * Checks whether the passed file uses PHP 7.0's scalar type hints
   * For example:
   *
   *   function(int $x) {}
   *
   * @param File $file the phpcs file handle to check
   * @return bool true if any uses were found
   */
  public function usesScalarTypeHints(File $file): bool
  {
    foreach(FileUtils::findAllByTypes($file,[T_FUNCTION,T_CLOSURE]) as $current)
      foreach($file->getMethodParameters($current) as $properties)
        if(in_array(strtolower($properties['type_hint']),['string','int','float','bool']))
          return true;
    return false;
  }

  /**
   * Checks whether the passed file uses PHP 7.0's return type declarations
   * For example:
   *
   *   function asdf(): int {}
   *
   * @param File $file the phpcs file handle to check
   * @return bool true if any uses were found
   */
  public function usesReturnTypes(File $file): bool
  {
    foreach(FileUtils::findAllByTypes($file,[T_FUNCTION,T_CLOSURE]) as $current)
      if(FileUtils::findReturnType($file,$current)!==NULL)
        return true;
    return false;
  }

  /**
   * Checks whether the passed file uses PHP 7.0's null coalescing
   * For example:
   *
   *   $hostname=$_SERVER['hostname'] ?? '(unknown)';
   *
   * @param File $file the phpcs file handle to check
   * @return bool true if any uses were found
   */
  public function usesNullCoalescing(File $file): bool
  {
    return $file->findNext(T_COALESCE,0)!==false;
  }

  /**
   * Checks whether the passed file uses PHP 7.0's spaceship operator
   * For example:
   *
   *   $comparison=$a <=> $b;
   *
   * @param File $file the phpcs file handle to check
   * @return bool true if any uses were found
   */
  public function usesSpaceship(File $file): bool
  {
    return $file->findNext(T_SPACESHIP,0)!==false;
  }

  /**
   * Checks whether the passed file uses PHP 7.0's constant array defines
   * For example:
   *
   *   define('BITS',[0,1]);
   *
   * @param File $file the phpcs file handle to check
   * @return bool true if any uses were found
   */
  public function usesArrayDefines(File $file): bool
  {
    $tokens=$file->getTokens();
    foreach(FileUtils::findAllByTypes($file,[T_OPEN_PARENTHESIS]) as $current)
    {
      $prev=$file->findPrevious(Tokens::$emptyTokens,$current-1,NULL,true);
      if($prev===false || $tokens[$prev]['code']!==T_STRING || $tokens[$prev]['content']!=='define' || $file->findStartOfStatement($prev)!=$prev)
        continue;
      if($file->findNext([T_OPEN_SHORT_ARRAY,T_ARRAY],$current,$tokens[$current]['parenthesis_closer'])!==false)
        return true;
    }
    return false;
  }

  /**
   * Checks whether the passed file uses PHP 7.0's anonymous classes
   * For example:
   *
   *   $x=new class {};
   *
   * @param File $file the phpcs file handle to check
   * @return bool true if any uses were found
   */
  public function usesAnonymousClasses(File $file): bool
  {
    $tokens=$file->getTokens();
    foreach(FileUtils::findAllByTypes($file,[T_NEW]) as $current)
    {
      $next=$file->findNext(Tokens::$emptyTokens,$current+1,NULL,true);
      if($next!==false && $tokens[$next]['code']===T_ANON_CLASS)
        return true;
    }
    return false;
  }

  /**
   * Checks whether the passed file uses PHP 7.0's grouped namespace imports
   * For example:
   *
   *   use A\{B,C};
   *
   * @param File $file the phpcs file handle to check
   * @return bool true if any uses were found
   */
  public function usesGroupedNamespaceImports(File $file): bool
  {
    $tokens=$file->getTokens();
    foreach(FileUtils::findAllByTypes($file,[T_USE]) as $current)
    {
      if(!empty($tokens[$current]['conditions']))
        continue;
      $end=$file->findEndOfStatement($current);
      if($end===false)
        throw new \DomainException('cannot parse namespace import');
      if(strpos($file->getTokensAsString($current,$end-$current),'{')!==false)
        return true;
    }
    return false;
  }

  /**
   * Checks whether the passed file uses PHP 7.0's generator return expressions
   * For example:
   *
   *   $generator=(function() {
   *     yield 1;
   *     return 2;  //<---
   *   })();
   *
   * @param File $file the phpcs file handle to check
   * @return bool true if any uses were found
   */
  public function usesGeneratorReturn(File $file): bool
  {
    $tokens=$file->getTokens();
    foreach(FileUtils::findAllByTypes($file,[T_YIELD]) as $current)
    {
      $opener=$file->getCondition($current,T_CLOSURE);
      if($opener===false)
        $opener=$file->getCondition($current,T_FUNCTION);
      if($opener===false)
        throw new \LogicException('cannot parse generator');

      //XXX this will generate false positives with nested closures
      $return=$file->findNext(T_RETURN,$current+1,$tokens[$opener]['scope_closer']);

      if($return!==false)
        return true;
    }
    return false;
  }

  /**
   * Checks whether the passed file uses PHP 7.0's generator delegation
   * For example:
   *
   *   $f1=function() {
   *     yield 2;
   *   };
   *
   *   $f2=function() {
   *     yield 1;
   *     yield from $f1();   //<---
   *   };
   *
   * @param File $file the phpcs file handle to check
   * @return bool true if any uses were found
   */
  public function usesGeneratorDelegation(File $file): bool
  {
    return $file->findNext(T_YIELD_FROM,0)!==false;
  }
}
