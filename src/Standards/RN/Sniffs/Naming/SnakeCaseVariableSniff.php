<?php
declare(strict_types=1);
/**
 * requires PHP version 7.0+
 * @author Richard Nusser
 * @copyright 2017 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 * @link https://github.com/rinusser/CodeSnifferUtils
 */

namespace RN\CodeSnifferUtils\Sniffs\Naming;

use PHP_CodeSniffer\Sniffs\AbstractVariableSniff;
use PHP_CodeSniffer\Files\File;
use RN\CodeSnifferUtils\Utils\PerFileSniffConfig;
use RN\CodeSnifferUtils\Utils\FileUtils;

/**
 * Ensures variables are named properly:
 *  - must only contain lowercase letters, digits and underscores
 *  - must start with a letter
 */
class SnakeCaseVariableSniff extends AbstractVariableSniff
{
  public const SUPERGLOBALS=['$_SERVER','$_GET','$_POST','$_REQUEST','$_SESSION','$_ENV','$_COOKIE','$_FILES','$GLOBALS','$HTTP_RAW_POST_DATA'];

  //import per-file config
  use PerFileSniffConfig;

  protected function processVariable(File $file, $stack_ptr)  //CSU.IgnoreName: required by parent class
  {
    $tokens=$file->getTokens();
    $displayed_name=$tokens[$stack_ptr]['content'];

    if($this->_isDisabledInFile($file) || in_array($displayed_name,self::SUPERGLOBALS) || FileUtils::isStaticPropertyAccess($file,$stack_ptr))
      return;

    $name=ltrim($displayed_name,'$');
    $outermost_wrapping_function=NULL;

    $prev=$stack_ptr;
    while($prev>0)
    {
      $prev=$file->findPrevious([T_FUNCTION,T_CLOSURE],$prev-1,NULL,false);
      if($prev===false)
        break;

      if($tokens[$prev]['code']===T_CLOSURE && in_array($displayed_name,FileUtils::getClosureImports($file,$prev)))
        return;

      $is_in_parameters=$tokens[$prev]['parenthesis_opener']<$stack_ptr && $tokens[$prev]['parenthesis_closer']>$stack_ptr;
      $is_in_scope=!empty($tokens[$prev]['scope_opener']) && $tokens[$prev]['scope_opener']<$stack_ptr && $tokens[$prev]['scope_closer']>$stack_ptr;

      if(!$is_in_parameters && !$is_in_scope)
        continue;

      if($this->_hasVariableInFunctionParameters($file,$prev,$displayed_name))
        return;

      if($is_in_scope)
        $outermost_wrapping_function=$prev;
    }

    if($this->_isRepeatedOccurrence($file,$outermost_wrapping_function,$stack_ptr,$displayed_name))
      return;

    if(!ctype_lower($name[0]))
    {
      $error='Variable "%s" must start with a lowercase letter';
      $file->addError($error,$stack_ptr,'InvalidStart',[$displayed_name]);
      return;
    }

    NameChecker::checkSnakeCase($file,$stack_ptr,'variable',$name,$displayed_name);
  }

  private function _isRepeatedOccurrence(File $file, $function_offset, int $variable_offset, string $name)
  {
    if(!$function_offset)
      return false;

    $prev=$file->findPrevious(T_VARIABLE,$variable_offset-1,NULL,false,$name);
    return $prev!==false && $prev>=$file->getTokens()[$function_offset]['scope_opener'];
  }

  private function _hasVariableInFunctionParameters(File $file, int $offset, string $name)
  {
    $function_parameters=$file->getMethodParameters($offset);
    foreach($function_parameters as $parameter)
      if($parameter['name']===$name)
        return true;
    return false;
  }


  protected function processVariableInString(File $file, $stack_ptr)  //CSU.IgnoreName: required by parent class
  {
  }

  protected function processMemberVar(File $file, $stack_ptr)  //CSU.IgnoreName: required by parent class
  {
  }
}
