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
use RN\CodeSnifferUtils\Utils\PerFileSniffConfig;
use RN\CodeSnifferUtils\Utils\NoImplicitProperties;

/**
 * Ensures commas and semicolons aren't preceded by any whitespaces
 */
class SeparatorSniff implements Sniff
{
  use PerFileSniffConfig;

  //disallow access to undeclared properties
  use NoImplicitProperties;

  private static $_tokens=[T_COMMA=>'Comma',T_SEMICOLON=>'Semicolon'];

  /**
   * Gets called by phpcs to return a list of tokens types to wait for
   *
   * @return array the list of token types
   */
  public function register()
  {
    return array_keys(self::$_tokens);
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

    $tokens=$file->getTokens();
    if($tokens[$stack_ptr-1]['code']===T_WHITESPACE)
    {
      $error='Commas and semicolons must not follow whitespaces of any kind';
      $file->addError($error,$stack_ptr,'SpaceBefore'.$this->_getTokenName($tokens[$stack_ptr]['code']));
    }
  }

  private function _getTokenName($type): string
  {
    if(!isset(self::$_tokens[$type]))
      throw new \LogicException("unhandled token type ($type)");
    return self::$_tokens[$type];
  }
}
