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
use RN\CodeSnifferUtils\Utils\PrecedingEmptyLinesChecker;

/**
 * Ensures closing curly brackets are preceded by the proper amount of newlines: none.
 */
class ClosingBracketSniff implements Sniff
{
  /**
   * Registers this sniff's triggers
   * @return array
   */
  public function register()
  {
    return [T_CLOSE_CURLY_BRACKET];
  }

  /**
   * @param File $phpcsFile the phpcs file handle to check
   * @param int  $stackPtr  the phpcs context
   * @return NULL to indicate phpcs should continue processing rest of file normally
   */
  public function process(File $phpcsFile, $stackPtr)
  {
    $allowed_by_type=[PrecedingEmptyLinesChecker::T_ANY=>[-1,0]];
    return (new PrecedingEmptyLinesChecker())->process($phpcsFile,$stackPtr,$allowed_by_type);
  }
}
