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
   * @param File $file      the phpcs file handle to check
   * @param int  $stack_ptr the phpcs context
   * @return NULL to indicate phpcs should continue processing rest of file normally
   */
  protected function processMemberVar(File $file, $stack_ptr)  //CSU.IgnoreName: required by parent class
  {
    $tokens=$file->getTokens();
    $displayed_name=$tokens[$stack_ptr]['content'];
    $name=ltrim($displayed_name,'$');

    $properties=$file->getMemberProperties($stack_ptr);
    if(!$properties)
      return;

    if(NameChecker::isSkipped($file,$stack_ptr))
      return;

    NameChecker::checkUnderscorePrefix($file,$stack_ptr,$properties['scope'],'property',$name,$displayed_name);
  }

  protected function processVariable(File $file, $stack_ptr)  //CSU.IgnoreName: required by parent class
  {
  }

  protected function processVariableInString(File $file, $stack_ptr)  //CSU.IgnoreName: required by parent class
  {
  }
}
