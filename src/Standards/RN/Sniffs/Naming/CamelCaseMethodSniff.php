<?php
declare(strict_types=1);
/**
 * requires PHP version 7.0+
 * @author Richard Nusser
 * @copyright 2017 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 */

namespace RN\CodeSnifferUtils\Sniffs\Naming;

use PHP_CodeSniffer\Standards\PSR1\Sniffs\Methods\CamelCapsMethodNameSniff;
use PHP_CodeSniffer\Files\File;

/**
 * Ensures class methods are named properly:
 *  - public methods must not start with an underscore (except for magic methods)
 *  - private/protected methods must start with an underscore
 *  - after the leading underscore (or not), method names must start with a lowercase letter and not contain any other underscores
 */
class CamelCaseMethodSniff extends CamelCapsMethodNameSniff
{
  /**
   * @param File $phpcsFile the phpcs file handle to check
   * @param int  $stackPtr  the phpcs context
   * @param int  $currScope the current scope token's offset
   * @return NULL to indicate phpcs should continue processing rest of file normally
   */
  protected function processTokenWithinScope(File $phpcsFile, $stackPtr, $currScope)  //CSU.IgnoreName: required by parent class
  {
    $name=$phpcsFile->getDeclarationName($stackPtr);
    if(!$name===NULL)
      return;

    if(NameChecker::isSkipped($phpcsFile,$stackPtr))
      return;

    if(substr($name,0,2)==='__')
    {
      $rest=strtolower(substr($name,2));
      if(isset($this->magicMethods[$rest])||isset($this->methodsDoubleUnderscore[$rest]))
        return;
    }

    $properties=$phpcsFile->getMethodProperties($stackPtr);

    if(NameChecker::checkUnderscorePrefix($phpcsFile,$stackPtr,$properties['scope'],'method',$name,$name.'()'))
      parent::processTokenWithinScope($phpcsFile,$stackPtr,$currScope);
  }
}
