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
 * This sniff checks for double semicolons (i.e. empty statements) outside for() loops.
 * It won't find double semicolons nested somewhere within for loops, for example:
 *
 *   for($x=function(){return 1;;};;)
 *
 * is technically an error but the closure's double semicolons currently aren't detected.
 */
class DoubleSemicolonSniff implements Sniff
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
    return [T_SEMICOLON];
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

    //in for() loops it's OK to have multiple semicolons, e.g. "for(;;)"
    if($this->_isForLoopSeparator($file,$stack_ptr))
      return;

    $prev=$file->findPrevious(Tokens::$emptyTokens,$stack_ptr-1,NULL,true);
    if($prev===false || $tokens[$prev]['code']!==T_SEMICOLON)
      return;

    $warning='Found double semicolons, there\'s a missing statement or extraneous semicolon';
    $file->addWarning($warning,$stack_ptr,'Found');
  }

  private function _isForLoopSeparator(File $file, int $stack_ptr): bool
  {
    $tokens=$file->getTokens();
    $parentheses=$tokens[$stack_ptr]['nested_parenthesis']??[];
    if(empty($parentheses))
      return false;
    $opener=array_keys($parentheses)[0];
    $owner=$tokens[$opener]['parenthesis_owner'];
    return $tokens[$owner]['code']===T_FOR;
  }
}
