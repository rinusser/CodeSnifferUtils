<?php
declare(strict_types=1);
/**
 * requires PHP version 7.0+
 * @author Richard Nusser
 * @copyright 2017 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 */

namespace RN\CodeSnifferUtils\Sniffs\Naming;

use PHP_CodeSniffer\Sniffs\AbstractVariableSniff;
use PHP_CodeSniffer\Files\File;

/**
 * Ensures properties are named properly:
 *  - public properties must not start with an underscore
 *  - private/protected properties must start with an underscore
 *  - after the leading underscore (or not), properties must start with a lowercase letter and not contain any other underscores
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
    $tokens=$phpcsFile->getTokens();
    $displayed_name=$tokens[$stackPtr]['content'];
    $name=ltrim($displayed_name,'$');

    $properties=$phpcsFile->getMemberProperties($stackPtr);
    if(!$properties)
      return;

    if(NameChecker::isSkipped($phpcsFile,$stackPtr))
      return;

    NameChecker::checkUnderscorePrefix($phpcsFile,$stackPtr,$properties['scope'],'property',$name,$displayed_name);
  }

  protected function processVariable(File $phpcsFile, $stackPtr)  //CSU.IgnoreName: required by parent class
  {
  }

  protected function processVariableInString(File $phpcsFile, $stackPtr)  //CSU.IgnoreName: required by parent class
  {
  }
}
