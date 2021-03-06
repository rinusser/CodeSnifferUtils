<?php
declare(strict_types=1);
/**
 * requires PHP version 7.1+
 * @author Richard Nusser
 * @copyright 2017-2018 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 * @link https://github.com/rinusser/CodeSnifferUtils
 */

namespace RN\CodeSnifferUtils\Standards\RN\Sniffs\Spacing;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;
use RN\CodeSnifferUtils\Checkers\PrecedingEmptyLinesChecker;
use RN\CodeSnifferUtils\Config\PerFileSniffConfig;
use RN\CodeSnifferUtils\Utils\NoImplicitProperties;

/**
 * Ensures use declarations are preceded by the proper amount of newlines
 */
class UseSniff implements Sniff
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
    return [T_USE];
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

    $allowed_by_type=[T_OPEN_TAG=>0,
                      T_DECLARE=>1,
                      T_NAMESPACE=>1,
                      T_USE=>0,
                      T_CLOSE_PARENTHESIS=>-1, //this is for lambda expressions
                      T_OPEN_CURLY_BRACKET=>0, //this is for trait imports
                      T_DOC_COMMENT_CLOSE_TAG=>1,
                      T_COMMENT=>[0,1]];
    return (new PrecedingEmptyLinesChecker($allowed_by_type))->process($file,$stack_ptr);
  }
}
