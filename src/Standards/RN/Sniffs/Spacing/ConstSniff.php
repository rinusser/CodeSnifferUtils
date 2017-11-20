<?php
declare(strict_types=1);
/**
 * requires PHP version 7.0+
 * @author Richard Nusser <do.not@con.tact>
 * @copyright 2017 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 */

namespace RN\CodeSnifferUtils\Sniffs\Spacing;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Util\Tokens;
use RN\CodeSnifferUtils\Utils\ContextAwarePrecedingEmptyLinesChecker;

/**
 * Ensures class constants are preceded by the proper amount of newlines
 */
class ConstSniff implements Sniff
{
  /**
   * Registers this sniff's triggers
   * @return array
   */
  public function register()
  {
    return [T_CONST];
  }

  /**
   * @param File $phpcsFile the phpcs file handle to check
   * @param int  $stackPtr  the phpcs context
   * @return NULL to indicate phpcs should continue processing rest of file normally
   */
  public function process(File $phpcsFile, $stackPtr)
  {
    $allowed_by_type=[T_OPEN_CURLY_BRACKET=>0,
                      T_SEMICOLON=>[0,2]];
    return (new ContextAwarePrecedingEmptyLinesChecker(T_CONST))->process($phpcsFile,$stackPtr,$allowed_by_type);
  }
}
