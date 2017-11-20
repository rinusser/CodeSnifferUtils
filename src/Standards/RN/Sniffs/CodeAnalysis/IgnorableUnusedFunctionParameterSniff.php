<?php
declare(strict_types=1);
/**
 * requires PHP version 7.0+
 * @author Richard Nusser <do.not@con.tact>
 * @copyright 2017 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 */

namespace RN\CodeSnifferUtils\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Standards\Generic\Sniffs\CodeAnalysis\UnusedFunctionParameterSniff;
use PHP_CodeSniffer\Files\File;
use RN\CodeSnifferUtils\Utils\IgnorableUnusedFunctionParameterFileProxy;

/**
 * This is based on Generic.CodeAnalysis.UnusedFunctionParameterSniff, but minds knowingly unused function parameters
 */
class IgnorableUnusedFunctionParameterSniff extends UnusedFunctionParameterSniff
{
  /**
   * @param File $phpcsFile the phpcs file handle to check
   * @param int  $stackPtr  the phpcs context
   * @return mixed see parent class
   */
  public function process(File $phpcsFile, $stackPtr)
  {
    $proxy=new IgnorableUnusedFunctionParameterFileProxy($phpcsFile);
    return parent::process($proxy,$stackPtr);
  }
}
