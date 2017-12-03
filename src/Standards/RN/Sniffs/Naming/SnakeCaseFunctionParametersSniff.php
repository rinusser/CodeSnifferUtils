<?php
declare(strict_types=1);
/**
 * requires PHP version 7.0+
 * @author Richard Nusser
 * @copyright 2017 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 */

namespace RN\CodeSnifferUtils\Sniffs\Naming;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;
use RN\CodeSnifferUtils\Utils\PerFileSniffConfig;

/**
 * Ensures function parameters are snake_case and start with a lowercase letter
 */
class SnakeCaseFunctionParametersSniff implements Sniff
{
  use PerFileSniffConfig;

  /**
   * Registers tokens to listen for: functions and closures
   * @return NULL
   */
  public function register()
  {
    return [T_FUNCTION,T_CLOSURE];
  }

  /**
   * Processes found tokens
   *
   * @param File $file      the phpcs file handle to check
   * @param int  $stack_ptr the function's token offset
   * @return NULL
   */
  public function process(File $file, $stack_ptr)
  {
    if($this->_isDisabledInFile($file))
      return;

    $parameters=$file->getMethodParameters($stack_ptr);
    foreach($parameters as $parameter)
    {
      $name=ltrim($parameter['name'],'$');

      if(!ctype_lower($name[0]))
      {
        $error='Function/method parameter "%s" must start with a lowercase letter';
        $file->addError($error,$parameter['token'],'InvalidStart',[$name]);
        continue;
      }

      NameChecker::checkSnakeCase($file,$stack_ptr,'function/method parameter',$name);
    }
  }
}
