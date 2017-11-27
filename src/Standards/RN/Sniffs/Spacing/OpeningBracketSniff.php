<?php
declare(strict_types=1);
/**
 * requires PHP version 7.0+
 * @author Richard Nusser
 * @copyright 2017 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 */

namespace RN\CodeSnifferUtils\Sniffs\Spacing;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;
use RN\CodeSnifferUtils\Utils\TokenNames;

/**
 * Ensures opening curly brackets are succeded by the proper amount of newlines: none.
 */
class OpeningBracketSniff implements Sniff
{
  /**
   * Registers this sniff's triggers
   * @return array
   */
  public function register()
  {
    return [T_OPEN_CURLY_BRACKET];
  }

  /**
   * @param File $phpcsFile the phpcs file handle to check
   * @param int  $stackPtr  the phpcs context
   * @return NULL to indicate phpcs should continue processing rest of file normally
   */
  public function process(File $phpcsFile, $stackPtr)
  {
    $tokens=$phpcsFile->getTokens();
    $next=$phpcsFile->findNext(T_WHITESPACE,$stackPtr+1,NULL,true);
    $lines_between=$tokens[$next]['line']-$tokens[$stackPtr]['line']-1;
    if($lines_between>0)
    {
      $currents_name=TokenNames::getPrintableName($tokens[$stackPtr]['code'],$tokens[$stackPtr]['type']);
      $error='Expected 0 empty lines after '.$currents_name.', got '.$lines_between.' instead';
      $phpcsFile->addError($error,$stackPtr,'SucceedingNewlines');
    }
    return NULL;
  }
}
