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
use RN\CodeSnifferUtils\Config\PerFileSniffConfig;
use RN\CodeSnifferUtils\Config\PropertyCast;
use RN\CodeSnifferUtils\Utils\NoImplicitProperties;
use RN\CodeSnifferUtils\Files\FileUtils;

/**
 * Ensures commas and semicolons aren't preceded by any whitespaces
 */
class SeparatorSniff implements Sniff
{
  use PerFileSniffConfig;

  //disallow access to undeclared properties
  use NoImplicitProperties;

  private static $_tokens=[T_COMMA=>'Comma',T_SEMICOLON=>'Semicolon'];


  public $includeFunctionCallCommas=false;


  /**
   * Gets called by phpcs to return a list of tokens types to wait for
   *
   * @return array the list of token types
   */
  public function register()
  {
    $this->includeFunctionCallCommas=PropertyCast::toBool($this->includeFunctionCallCommas,'includeFunctionCallCommas');
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
    if($tokens[$stack_ptr-1]['code']!==T_WHITESPACE || !$this->includeFunctionCallCommas && $this->_isFunctionCallComma($file,$stack_ptr))
      return;
    $error='Commas and semicolons must not follow whitespaces of any kind';

    if($tokens[$stack_ptr]['code']===T_SEMICOLON)
    {
      $prev=$file->findPrevious(T_WHITESPACE,$stack_ptr-1,NULL,true);
      if($prev!==false && $tokens[$prev]['code']===T_SEMICOLON && $tokens[$prev]['line']!=$tokens[$stack_ptr]['line'])
      {
        $file->addError($error,$stack_ptr,'SpaceBefore'.$this->_getTokenName($tokens[$stack_ptr]['code']));
        return;
      }
    }

    $fix=$file->addFixableError($error,$stack_ptr,'SpaceBefore'.$this->_getTokenName($tokens[$stack_ptr]['code']));
    if($fix)
    {
      $pos=$stack_ptr;
      $file->fixer->beginChangeSet();
      while($tokens[--$pos]['code']===T_WHITESPACE)
        $file->fixer->replaceToken($pos,'');
      $file->fixer->endChangeSet();
    }
  }

  protected function _isFunctionCallComma(File $file, int $comma): bool
  {
    $open_parenthesis=$file->findPrevious(T_OPEN_PARENTHESIS,$comma-1,NULL,false,NULL,true);
    if($open_parenthesis===false)
      return false;
    $last_callee_token=FileUtils::findLastCalleeToken($file,$open_parenthesis);
    if($last_callee_token===false)
      return false;

    $separating_commas=FileUtils::getSeparatingCommas($file,$open_parenthesis);
    return in_array($comma,$separating_commas);
  }

  private function _getTokenName($type): string
  {
    if(!isset(self::$_tokens[$type]))
      throw new \LogicException("unhandled token type ($type)");
    return self::$_tokens[$type];
  }
}
