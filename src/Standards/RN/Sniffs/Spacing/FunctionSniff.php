<?php
declare(strict_types=1);
/**
 * requires PHP version 7.1+
 * @author Richard Nusser
 * @copyright 2017 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 * @link https://github.com/rinusser/CodeSnifferUtils
 */

namespace RN\CodeSnifferUtils\Standards\RN\Sniffs\Spacing;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;
use RN\CodeSnifferUtils\Checkers\ContextAwarePrecedingEmptyLinesChecker;
use RN\CodeSnifferUtils\Config\PerFileSniffConfig;
use RN\CodeSnifferUtils\Utils\NoImplicitProperties;

/**
 * Ensures function/method declarations are preceded by the proper amount of newlines
 */
class FunctionSniff implements Sniff
{
  use PerFileSniffConfig;

  //disallow access to undeclared properties
  use NoImplicitProperties;


  /**
   * Gets called by phpcs to return a list of tokens types to wait for
   *
   * @return array the list of token types
   */
  public function register()
  {
    return [T_FUNCTION];
  }

  /**
   * Gets called by phpcs to handle a file's token
   *
   * @param File $file      the phpcs file handle
   * @param int  $stack_ptr the token offset to be processed
   * @return int|NULL an indicator for phpcs whether to process the rest of the file normally
   */
  public function process(File $file, $stack_ptr)
  {
    if($this->_isDisabledInFile($file))
      return $file->numTokens;

    $allowed_by_type=[T_OPEN_CURLY_BRACKET=>0,
                      T_CLOSE_CURLY_BRACKET=>[1,2],
                      T_OPEN_TAG=>[0,1],
                      T_CLOSE_TAG=>[0,2],
                      T_SEMICOLON=>[0,2]];
    $checker=new ContextAwarePrecedingEmptyLinesChecker($allowed_by_type,T_FUNCTION,[T_ABSTRACT,T_STATIC,T_FINAL]);
    $checker->setFetcherAfterSemicolon([$this,'fetcher']);
    return $checker->process($file,$stack_ptr);
  }

  /**
   * Used to fetch distances between methods, depending on whether they're static or not
   *
   * @param File $file     the phpcs file handler
   * @param int  $current  the current token offset
   * @param int  $previous the previous token offset
   * @return array|NULL the distance range, or NULL if it's another combination of tokens
   */
  public function fetcher(File $file, int $current, int $previous)
  {
    $previous_properties=$file->getMethodProperties($previous);
    $current_properties=$file->getMethodProperties($current);
    if($previous_properties['is_abstract']==$current_properties['is_abstract'])
      return $previous_properties['is_static']==$current_properties['is_static']?[0,2]:[1,2];
    else
      return [1,2];
  }
}
