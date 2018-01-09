<?php
declare(strict_types=1);
/**
 * requires PHP version 7.1+
 * @author Richard Nusser
 * @copyright 2017-2018 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 * @link https://github.com/rinusser/CodeSnifferUtils
 */

namespace RN\CodeSnifferUtils\Standards\RN\Sniffs\Naming;

use PHP_CodeSniffer\Standards\PSR1\Sniffs\Methods\CamelCapsMethodNameSniff;
use PHP_CodeSniffer\Files\File;
use RN\CodeSnifferUtils\Config\PerFileSniffConfig;
use RN\CodeSnifferUtils\Utils\NoImplicitProperties;

/**
 * Ensures class methods are named properly:
 *  - public methods must not start with an underscore (except for magic methods)
 *  - private/protected methods must start with an underscore
 *  - after the leading underscore (or not), method names must start with a lowercase letter and not contain any other underscores
 */
class CamelCaseMethodSniff extends CamelCapsMethodNameSniff
{
  use PerFileSniffConfig;

  //disallow access to undeclared properties
  use NoImplicitProperties;


  /**
   * Gets called by parent class, processes tokens
   *
   * @param File $file       the phpcs file handle to check
   * @param int  $stack_ptr  the phpcs context
   * @param int  $curr_scope the current scope token's offset
   * @return NULL to indicate phpcs should continue processing rest of file normally
   */
  protected function processTokenWithinScope(File $file, $stack_ptr, $curr_scope)  //CSU.IgnoreName: required by parent class
  {
    if($this->_isDisabledInFile($file))
      return $file->numTokens;

    $name=$file->getDeclarationName($stack_ptr);
    if(!$name===NULL)
      return;

    if(NameChecker::isSkipped($file,$stack_ptr))
      return;

    if(substr($name,0,2)==='__')
    {
      $rest=strtolower(substr($name,2));
      if(isset($this->magicMethods[$rest])||isset($this->methodsDoubleUnderscore[$rest]))
        return;
    }

    $properties=$file->getMethodProperties($stack_ptr);

    if(NameChecker::checkUnderscorePrefix($file,$stack_ptr,$properties['scope'],'method',$name,$name.'()'))
      parent::processTokenWithinScope($file,$stack_ptr,$curr_scope);
  }
}
