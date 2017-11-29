<?php
declare(strict_types=1);
/**
 * requires PHP version 7.0+
 * @author Richard Nusser
 * @copyright 2017 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 */

namespace RN\CodeSnifferUtils\Sniffs\Spacing;

use PHP_CodeSniffer\Sniffs\AbstractVariableSniff;
use PHP_CodeSniffer\Files\File;
use RN\CodeSnifferUtils\Utils\ContextAwarePrecedingEmptyLinesChecker;

/**
 * Ensures property declarations are preceded by the proper amount of newlines
 */
class PropertySniff extends AbstractVariableSniff
{
  /**
   * @param File $phpcsFile the phpcs file handle to check
   * @param int  $stackPtr  the phpcs context
   * @return NULL to indicate phpcs should continue processing rest of file normally
   */
  protected function processMemberVar(File $phpcsFile, $stackPtr)  //CSU.IgnoreName: required by parent class
  {
    $allowed_by_type=[T_OPEN_CURLY_BRACKET=>0,
                      T_CLOSE_CURLY_BRACKET=>[1,2],
                      T_COMMA=>[-1,0],
                      T_VARIABLE=>[-1,0],
                      T_COMMENT=>[0,1],
                      T_SEMICOLON=>[0,2]];
    return (new ContextAwarePrecedingEmptyLinesChecker(T_VARIABLE,[T_STATIC]))->process($phpcsFile,$stackPtr,$allowed_by_type);
  }

  protected function processVariable(File $phpcsFile, $stackPtr)  //CSU.IgnoreName: required by parent class
  {
  }

  protected function processVariableInString(File $phpcsFile, $stackPtr)  //CSU.IgnoreName: required by parent class
  {
  }
}
