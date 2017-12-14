<?php
declare(strict_types=1);
/**
 * requires PHP version 7.0+
 * @author Richard Nusser
 * @copyright 2017 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 * @link https://github.com/rinusser/CodeSnifferUtils
 */

namespace RN\CodeSnifferUtils\Standards\RN\Sniffs\Naming;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;
use RN\CodeSnifferUtils\Config\PerFileSniffConfig;
use RN\CodeSnifferUtils\Utils\NoImplicitProperties;

/**
 * Ensures function parameters are snake_case and start with a lowercase letter
 */
class SnakeCaseFunctionParametersSniff implements Sniff
{
  use PerFileSniffConfig;

  //disallow access to undeclared properties
  use NoImplicitProperties;


  /**
   * Gets called by phpcs to return a list of tokens types to wait for
   *
   * @return array the list of token types
   */
  public function register()
  {
    return [T_FUNCTION,T_CLOSURE];
  }

  /**
   * Gets called by phpcs to handle a file's token
   *
   * @param File $file      the phpcs file handle
   * @param int  $stack_ptr the token offset to be processed
   * @return int|NULL an indicator for phpcs whether to process the rest of the file normally
   */
  public function process(File $file, $stack_ptr)
  {
    if($this->_isDisabledInFile($file))
      return $file->numTokens;

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
