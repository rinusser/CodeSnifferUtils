<?php
declare(strict_types=1);
/**
 * requires PHP version 7.1+
 * @author Richard Nusser
 * @copyright 2017-2018 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 * @link https://github.com/rinusser/CodeSnifferUtils
 */

namespace RN\CodeSnifferUtils\Checkers\FileVersion;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Util\Tokens;
use RN\CodeSnifferUtils\Files\FileUtils;

/**
 * File version checks for PHP 7.1
 */
class PHP71 extends AbstractVersion
{
  /**
   * Gets called automatically to determine what PHP version we're checking in this class
   *
   * @return string the PHP version
   */
  public function getVersion(): string
  {
    return '7.1';
  }

  /**
   * Checks whether the passed file uses PHP 7.1's "iterable" or nullable type hints
   * For example:
   *
   *   function foo(?string $x) {}
   *   function bar(iterable $list) {}
   *
   * @param File $file the phpcs file handle to check
   * @return bool true if any uses were found
   */
  public function usesIterableOrNullableTypeHint(File $file): bool
  {
    foreach(FileUtils::findAllByTypes($file,[T_FUNCTION,T_CLOSURE]) as $current)
      foreach($file->getMethodParameters($current) as $properties)
        if($properties['nullable_type'] || strtolower(ltrim($properties['type_hint'],'?'))==='iterable')
          return true;
    return false;
  }

  /**
   * Checks whether the passed file uses PHP 7.1's "void" or "iterable" return types
   * For example:
   *
   *   function foo(): void {}
   *   function bar(): iterable {return [];}
   *
   * @param File $file the phpcs file handle to check
   * @return bool true if any uses were found
   */
  public function usesVoidOrIterableReturnType(File $file): bool
  {
    foreach(FileUtils::findAllByTypes($file,[T_FUNCTION,T_CLOSURE]) as $current)
    {
      $type=FileUtils::findReturnType($file,$current);
      if($type===NULL)
        continue;
      if(in_array(strtolower(ltrim($type,'?')),['void','iterable']))
        return true;
    }
    return false;
  }

  /**
   * Checks whether the passed file uses PHP 7.1's array destructuring
   * For example:
   *
   *   [$a,$b]=range(1,2);
   *
   * @param File $file the phpcs file handle to check
   * @return bool true if any uses were found
   */
  public function usesArrayDestructuring(File $file): bool
  {
    $tokens=$file->getTokens();
    foreach(FileUtils::findAllByTypes($file,[T_CLOSE_SQUARE_BRACKET]) as $current)
    {
      $next=$file->findNext(Tokens::$emptyTokens,$current+1,NULL,true);
      if($next===false || $tokens[$next]['code']!==T_EQUAL)
        continue;
      $start=$file->findStartOfStatement($tokens[$current]['bracket_opener']);
      if($start!==$tokens[$current]['bracket_opener'])
        continue;
      return true;
    }
    return false;
  }

  /**
   * Checks whether the passed file uses PHP 7.1's foreach array destructuring
   * For example:
   *
   *   foreach($data as [$a,$b]) {...}
   *
   * @param File $file the phpcs file handle to check
   * @return bool true if any uses were found
   */
  public function usesForeachDestructuring(File $file): bool
  {
    $tokens=$file->getTokens();
    foreach(FileUtils::findAllByTypes($file,[T_FOREACH]) as $current)
    {
      $closer=$tokens[$current]['parenthesis_closer'];
      $as=$file->findNext(T_AS,$current+1,$closer);
      if($as===false)
        throw new \DomainException('could not parse foreach() loop');
      if($file->findNext([T_LIST,T_OPEN_SHORT_ARRAY],$as,$closer)!==false)
        return true;
    }
    return false;
  }

  /**
   * Checks whether the passed file uses PHP 7.1's class constant visibilities
   * For example:
   *
   *   class X
   *   {
   *     protected const A=1;
   *   }
   *
   * @param File $file the phpcs file handle to check
   * @return bool true if any uses were found
   */
  public function usesConstVisibility(File $file): bool
  {
    foreach(FileUtils::findAllByTypes($file,[T_CONST]) as $current)
    {
      $properties=FileUtils::getConstProperties($file,$current);
      if($properties['scope_specified'] || $properties['scope']!='public')
        return true;
    }
    return false;
  }

  /**
   * Checks whether the passed file uses PHP 7.1's multiple exception catching
   * For example:
   *
   *   try {...}
   *   catch(Exception1 | Exception2 $e) {...}
   *
   * @param File $file the phpcs file handle to check
   * @return bool true if any uses were found
   */
  public function usesMultipleExceptionCatch(File $file): bool
  {
    $tokens=$file->getTokens();
    foreach(FileUtils::findAllByTypes($file,[T_CATCH]) as $current)
    {
      $closer=$tokens[$current]['parenthesis_closer'];
      if($file->findNext([T_BITWISE_OR],$current,$closer)!==false)
        return true;
    }
    return false;
  }
}
