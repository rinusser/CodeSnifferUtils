<?php
declare(strict_types=1);
/**
 * requires PHP version 7.1+
 * @author Richard Nusser
 * @copyright 2017 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 * @link https://github.com/rinusser/CodeSnifferUtils
 */

namespace RN\CodeSnifferUtils\Standards\RN\Sniffs\Naming;

use PHP_CodeSniffer\Sniffs\AbstractScopeSniff;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Util\Tokens;
use RN\CodeSnifferUtils\Config\PerFileSniffConfig;
use RN\CodeSnifferUtils\Utils\NoImplicitProperties;

/**
 * Ensures functions outside classes are named properly:
 *  - must not start with a leading underscore (except for PHP magic methods)
 *  - must be in snake_case
 */
class SnakeCaseFunctionSniff extends AbstractScopeSniff
{
  use PerFileSniffConfig;

  //disallow access to undeclared properties
  use NoImplicitProperties;


  /**
   * Constructor, registers tokens to listen for: functions within/outside classes
   */
  public function __construct()
  {
    parent::__construct(Tokens::$ooScopeTokens,[T_FUNCTION],true);
  }


  protected function processTokenWithinScope(File $file, $stack_ptr, $curr_scope)  //CSU.IgnoreName: required by parent class
  {
  }

  protected function processTokenOutsideScope(File $file, $stack_ptr)  //CSU.IgnoreName: required by parent class
  {
    if($this->_isDisabledInFile($file))
      return $file->numTokens;

    $name=$file->getDeclarationName($stack_ptr);
    if(!$name)
      return;

    if(substr($name,0,1)==='_' && $name!=='__autoload')
    {
      $error='Function "%s" must not start with an underscore';
      $file->addError($error,$stack_ptr,'LeadingUnderscore',[$name]);
      return;
    }

    NameChecker::checkSnakeCase($file,$stack_ptr,'function',$name);
  }
}
