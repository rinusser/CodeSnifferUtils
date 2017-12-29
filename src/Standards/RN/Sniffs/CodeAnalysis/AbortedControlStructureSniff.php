<?php
declare(strict_types=1);
/**
 * requires PHP version 7.1+
 * @author Richard Nusser
 * @copyright 2017 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 * @link https://github.com/rinusser/CodeSnifferUtils
 */

namespace RN\CodeSnifferUtils\Standards\RN\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;
use RN\CodeSnifferUtils\Config\PerFileSniffConfig;
use RN\CodeSnifferUtils\Utils\NoImplicitProperties;

/**
 * This sniff checks for inadvertently aborted control structure scopes
 */
class AbortedControlStructureSniff implements Sniff
{
  use PerFileSniffConfig;

  //disallow access to undeclared properties
  use NoImplicitProperties;


  /**
   * Called by phpcs, this returns the list of tokens to listen for
   *
   * @return array
   */
  public function register()
  {
    return [T_IF,T_ELSE,T_ELSEIF,T_FOREACH,T_WHILE,T_SWITCH,T_FOR];
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

    if($this->_isDoWhile($file,$stack_ptr))
      return;

    $start=$tokens[$stack_ptr]['parenthesis_closer']??$stack_ptr;
    $next=$file->findNext(Tokens::$emptyTokens,$start+1,NULL,true);
    if($next===false || $tokens[$next]['code']!==T_SEMICOLON)
      return;

    $warning='Found possibly prematurely aborted %s control statement';
    $file->addWarning($warning,$stack_ptr,'Found',[$tokens[$stack_ptr]['type']]);
  }

  private function _isDoWhile(File $file, int $stack_ptr): bool
  {
    $tokens=$file->getTokens();
    if($tokens[$stack_ptr]['code']!==T_WHILE)
      return false;

    $prev=$file->findPrevious(Tokens::$emptyTokens,$stack_ptr-1,NULL,true);
    if($prev===false || !in_array($tokens[$prev]['code'],[T_SEMICOLON,T_CLOSE_CURLY_BRACKET],true))
      return false;

    if($tokens[$prev]['code']===T_CLOSE_CURLY_BRACKET)
      $start=$tokens[$prev]['scope_condition'];
    else
      $start=$file->findStartOfStatement($prev-1);

    return $start!==false && $tokens[$start]['code']===T_DO;
  }
}
