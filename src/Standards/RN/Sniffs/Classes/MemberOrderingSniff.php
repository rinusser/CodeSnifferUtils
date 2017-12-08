<?php
declare(strict_types=1);
/**
 * requires PHP version 7.0+
 * @author Richard Nusser
 * @copyright 2017 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 */

//namespace RN\CodeSnifferUtils\Sniffs\Classes;
namespace PHP_CodeSniffer\RN\Sniffs\Classes; //for phpcs property injection

use PHP_CodeSniffer\Sniffs\AbstractScopeSniff;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Exceptions\TokenizerException;
use RN\CodeSnifferUtils\Utils\TokenNames;
use RN\CodeSnifferUtils\Utils\PerFileSniffConfig;
use RN\CodeSnifferUtils\Utils\ClassMemberChecker;

/**
 * Ensures class members are ordered correctly, by default:
 *   1. consts
 *   2. trait uses
 *   3. static properties
 *   4. static methods
 *   5. instance properties
 *   6. constructor
 *   7. instance methods
 */
class MemberOrderingSniff extends AbstractScopeSniff
{
  use PerFileSniffConfig;
  use ClassMemberChecker;

  public $constOrder=10;
  public $traitUseOrder=15;
  public $staticPropertyOrder=20;
  public $staticMethodOrder=30;
  public $instancePropertyOrder=40;
  public $constructorOrder=45;
  public $instanceMethodOrder=50;


  /**
   * Registers class parts to trigger on
   */
  public function __construct()
  {
    parent::__construct([T_CLASS],[T_CONST,T_VARIABLE,T_FUNCTION,T_USE]);
  }

  protected function _validateOrderProperties()
  {
    foreach(get_object_vars($this) as $tk=>$tv)
    {
      if(substr($tk,-5)!=='Order')
        continue;
      if(is_int($tv) || is_string($tv) && is_numeric($tv))
        continue;
      throw new \InvalidArgumentException("property '$tk' must a number, got '$tv' instead");
    }
  }

  /**
   * Processes the function tokens within the class.
   *
   * @param File $file       The file where this token was found.
   * @param int  $stack_ptr  The position where the token was found.
   * @param int  $curr_scope (unused) The current scope opener token.
   * @return void
   */
  protected function processTokenWithinScope(File $file, $stack_ptr, $curr_scope)  //CSU.IgnoreName: required by parent class
  {
    if($this->_isDisabledInFile($file))
      return $file->numTokens;

    $this->_validateOrderProperties(); //there's no hook in AbstractScopeSniff that allows for doing this just once per instance

    $tokens=$file->getTokens();
    try
    {
      [$order,$name,$error_prefix]=$this->_getProcessParameters($file,$stack_ptr);
    }
    catch(TokenizerException $e)
    {
      return;
    }

    if($order===NULL)
      return;

    $error_description=$this->_checkTokenOrdering($file,$stack_ptr,$order);

    if($error_description)
      $file->addError($name.' must be declared before any '.$error_description,$stack_ptr,$error_prefix.'TooLate');
  }

  private function _getProcessParameters(File $file, int $stack_ptr): array
  {
    $tokens=$file->getTokens();
    $order=NULL;

    switch($tokens[$stack_ptr]['code'])
    {
      case T_CONST:
        $order=$this->constOrder;
        $name='constants';
        $error_prefix='Const';
        break;
      case T_VARIABLE:
        //skip method arguments
        if(!empty($tokens[$stack_ptr]['nested_parenthesis']))
          return [NULL,NULL,NULL];
        [$order,$name,$error_prefix]=$this->_getVariableProcessParameters($file,$stack_ptr);
        break;
      case T_FUNCTION:
        [$order,$name,$error_prefix]=$this->_getFunctionProcessParameters($file,$stack_ptr);
        break;
      case T_USE:
        if(!$this->_isTraitImport($file)($stack_ptr))
          return [NULL,NULL,NULL];
        $order=$this->traitUseOrder;
        $name='trait uses';
        $error_prefix='TraitUse';
        break;
    }

    if($order===NULL)
      throw new \LogicException("unhandled token: ".TokenNames::getPrintableName($tokens[$stack_ptr]['code'],$tokens[$stack_ptr]['type']));

    return [$order,$name,$error_prefix];
  }


  private function _getVariableProcessParameters(File $file, int $stack_ptr): array
  {
    if($file->getMemberProperties($stack_ptr)['is_static'])
    {
      $order=$this->staticPropertyOrder;
      $name='static properties';
      $error_prefix='StaticProperty';
    }
    else
    {
      $order=$this->instancePropertyOrder;
      $name='instance properties';
      $error_prefix='InstanceProperty';
    }
    return [$order,$name,$error_prefix];
  }

  private function _getFunctionProcessParameters(File $file, int $stack_ptr): array
  {
    if($file->getMethodProperties($stack_ptr)['is_static'])
    {
      $order=$this->staticMethodOrder;
      $name='static methods';
      $error_prefix='StaticMethod';
    }
    else
    {
      if($this->_getMethodName($file,$stack_ptr)==='__construct')
      {
        $order=$this->constructorOrder;
        $name='constructor';
        $error_prefix='Constructor';
      }
      else
      {
        $order=$this->instanceMethodOrder;
        $name='instance methods';
        $error_prefix='InstanceMethod';
      }
    }
    return [$order,$name,$error_prefix];
  }

  protected function _checkTokenOrdering(File $file, int $stack_ptr, $order)
  {
    $checks=[[$this->constOrder,           'constants',          [$this,'_hasPrecedingConst']],
             [$this->traitUseOrder,        'trait uses',         [$this,'_hasPrecedingTraitUse']],
             [$this->staticPropertyOrder,  'static properties',  [$this,'_hasPrecedingStaticProperty']],
             [$this->staticMethodOrder,    'static methods',     [$this,'_hasPrecedingStaticMethod']],
             [$this->instancePropertyOrder,'instance properties',[$this,'_hasPrecedingInstanceProperty']],
             [$this->constructorOrder,     'constructor',        [$this,'_hasPrecedingConstructor']],
             [$this->instanceMethodOrder,  'instance methods',   [$this,'_hasPrecedingInstanceMethod']]];

    array_multisort($checks,array_column($checks,0));

    foreach($checks as [$threshold,$description,$checker])
      if($order<$threshold && $checker($file,$stack_ptr))
        return $description;

    return NULL;
  }

  /**
   * Required by parent class
   *
   * @param File $file      (unused)
   * @param int  $stack_ptr (unused)
   * @return void
   */
  protected function processTokenOutsideScope(File $file, $stack_ptr)  //CSU.IgnoreName: required by parent class
  {
  }
}
