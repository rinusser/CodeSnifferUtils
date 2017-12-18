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

use PHP_CodeSniffer\Sniffs\AbstractVariableSniff;
use PHP_CodeSniffer\Files\File;
use RN\CodeSnifferUtils\Checkers\ContextAwarePrecedingEmptyLinesChecker;
use RN\CodeSnifferUtils\Config\PerFileSniffConfig;
use RN\CodeSnifferUtils\Utils\NoImplicitProperties;

/**
 * Ensures property declarations are preceded by the proper amount of newlines
 */
class PropertySniff extends AbstractVariableSniff
{
  use PerFileSniffConfig;

  //disallow access to undeclared properties
  use NoImplicitProperties;


  /**
   * Gets called by phpcs to handle a file's token
   *
   * @param File $file      the phpcs file handle
   * @param int  $stack_ptr the token offset to be processed
   * @return int|NULL an indicator for phpcs whether to process the rest of the file normally
   */
  protected function processMemberVar(File $file, $stack_ptr)  //CSU.IgnoreName: required by parent class
  {
    if($this->_isDisabledInFile($file))
      return $file->numTokens;

    $allowed_by_type=[T_OPEN_CURLY_BRACKET=>0,
                      T_CLOSE_CURLY_BRACKET=>[1,2],
                      T_COMMA=>[-1,0],
                      T_VARIABLE=>[-1,0],
                      T_COMMENT=>[0,1],
                      T_SEMICOLON=>[0,2]];
    $fetcher=[$this,'fetcher'];
    return (new ContextAwarePrecedingEmptyLinesChecker($allowed_by_type,T_VARIABLE,[T_STATIC]))->setFetcherAfterSemicolon($fetcher)->process($file,$stack_ptr);
  }

  /**
   * Used to fetch distances between properties depending on whether they're static or not
   *
   * @param File $file     the phpcs file handler
   * @param int  $current  the current token offset
   * @param int  $previous the previous token offset
   * @return array|NULL the distance range, or NULL if it's another combination of tokens
   */
  public function fetcher(File $file, int $current, int $previous)
  {
    $previous_properties=$file->getMemberProperties($previous);
    $current_properties=$file->getMemberProperties($current);
    return $previous_properties['is_static']==$current_properties['is_static']?[0,1]:[1,2];
  }


  protected function processVariable(File $file, $stack_ptr)  //CSU.IgnoreName: required by parent class
  {
  }

  protected function processVariableInString(File $file, $stack_ptr)  //CSU.IgnoreName: required by parent class
  {
  }
}
