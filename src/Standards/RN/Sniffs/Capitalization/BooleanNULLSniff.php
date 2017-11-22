<?php
declare(strict_types=1);
/**
 * requires PHP version 7.0+
 * @author Richard Nusser
 * @copyright 2017 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 */

namespace RN\CodeSnifferUtils\Sniffs\Capitalization;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Util\Tokens;

/**
 * Ensures true and false are lowercase, NULL is uppercase
 */
class BooleanNULLSniff implements Sniff
{
  /**
   * Returns list of phpcs hooks this sniff should be triggered on
   * Called by phpcs automatically.
   *
   * @return array
   */
  public function register()
  {
    return [T_TRUE,T_FALSE,T_NULL];
  }

  /**
   * @param File $phpcsFile the phpcs file handle to check
   * @param int  $stackPtr  the phpcs context
   * @return NULL to indicate phpcs should continue processing rest of file normally
   */
  public function process(File $phpcsFile, $stackPtr)
  {
    $tokens=$phpcsFile->getTokens();
    $actual=$tokens[$stackPtr]['content'];
    $expected=strtolower($actual);
    if($expected==='null')
      $expected=strtoupper($actual);

    if($actual!==$expected)
    {
      $error='true and false should be lowercase, NULL should be uppercase. Got "'.$actual.'" instead.';
      $phpcsFile->addError($error,$stackPtr,'BooleanNULLCase');
    }
  }
}
