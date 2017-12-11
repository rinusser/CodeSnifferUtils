<?php
declare(strict_types=1);
/**
 * requires PHP version 7.0+
 * @author Richard Nusser
 * @copyright 2017 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 * @link https://github.com/rinusser/CodeSnifferUtils
 */

namespace RN\CodeSnifferUtils\Sniffs\Spacing;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;
use RN\CodeSnifferUtils\Utils\PrecedingEmptyLinesChecker;
use RN\CodeSnifferUtils\Utils\PerFileSniffConfig;
use RN\CodeSnifferUtils\Utils\NoImplicitProperties;

/**
 * Ensures closing curly brackets are preceded by the proper amount of newlines: none.
 */
class ClosingBracketSniff implements Sniff
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
    return [T_CLOSE_CURLY_BRACKET];
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

    $allowed_by_type=[PrecedingEmptyLinesChecker::T_ANY=>[-1,0]];
    return (new PrecedingEmptyLinesChecker())->process($file,$stack_ptr,$allowed_by_type);
  }
}
