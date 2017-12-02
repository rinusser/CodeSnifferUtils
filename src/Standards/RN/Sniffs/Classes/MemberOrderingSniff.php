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

/**
 * Ensures class members are ordered correctly, by default:
 *   1. consts
 *   2. static properties
 *   3. static methods
 *   4. instance properties
 *   5. instance methods
 */
class MemberOrderingSniff extends AbstractScopeSniff
{
  public $constOrder=10;
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
    parent::__construct([T_CLASS],[T_CONST,T_VARIABLE,T_FUNCTION]);
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
    $this->_validateOrderProperties(); //there's no hook in AbstractScopeSniff that allows for doing this just once per instance

    $tokens=$file->getTokens();
    $order=NULL;
    $name=NULL;
    $error_prefix=NULL;
    try
    {
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
            return;
          [$order,$name,$error_prefix]=$this->_getVariableProcessParameters($file,$stack_ptr);
          break;
        case T_FUNCTION:
          [$order,$name,$error_prefix]=$this->_getFunctionProcessParameters($file,$stack_ptr);
          break;
      }
    }
    catch(TokenizerException $e)
    {
      return;
    }

    if($order===NULL)
      throw new \LogicException("unhandled token: ".TokenNames::getPrintableName($tokens[$stack_ptr]['code'],$tokens[$stack_ptr]['type']));

    $error_description=$this->_checkTokenOrdering($file,$stack_ptr,$order);

    if($error_description)
      $file->addError($name.' must be declared before any '.$error_description,$stack_ptr,$error_prefix.'TooLate');
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


  protected function _hasPrecedingMember(File $file, $token, ?callable $member_check_func, bool $static, int $current, bool $constructor=false): bool
  {
    $tokens=$file->getTokens();
    while(true)
    {
      $current=$file->findPrevious([T_CLASS,$token],$current-1,NULL,false);
      if($current===false)
        break;
      if($tokens[$current]['code']===T_CLASS)
        break;
      if(!empty($tokens[$current]['nested_parenthesis']))
        continue;
      try
      {
        if(!$member_check_func)
          return true;
        if($member_check_func($current)['is_static']==$static)
        {
          if($tokens[$current]['code']===T_FUNCTION)
          {
            $is_constructor=$this->_getMethodName($file,$current)==='__construct';
            if($is_constructor!==$constructor)
              continue;
          }
          return true;
        }
      }
      catch(TokenizerException $e)
      {
        continue; //ignore: token wasn't a class member
      }
    }
    return false;
  }

  protected function _hasPrecedingConst(File $file, int $current): bool
  {
    return $this->_hasPrecedingMember($file,T_CONST,NULL,true,$current);
  }

  protected function _hasPrecedingStaticProperty(File $file, int $current): bool
  {
    return $this->_hasPrecedingMember($file,T_VARIABLE,[$file,'getMemberProperties'],true,$current);
  }

  protected function _hasPrecedingStaticMethod(File $file, int $current): bool
  {
    return $this->_hasPrecedingMember($file,T_FUNCTION,[$file,'getMethodProperties'],true,$current);
  }

  protected function _hasPrecedingInstanceProperty(File $file, int $current): bool
  {
    return $this->_hasPrecedingMember($file,T_VARIABLE,[$file,'getMemberProperties'],false,$current);
  }

  protected function _hasPrecedingConstructor(File $file, int $current): bool
  {
    return $this->_hasPrecedingMember($file,T_FUNCTION,[$file,'getMethodProperties'],false,$current,true);
  }

  protected function _hasPrecedingInstanceMethod(File $file, int $current): bool
  {
    return $this->_hasPrecedingMember($file,T_FUNCTION,[$file,'getMethodProperties'],false,$current,false);
  }


  private function _getMethodName(File $file, int $stack_ptr)
  {
    $next=$file->findNext([T_WHITESPACE],$stack_ptr+1,NULL,true);
    return $next!==false?$file->getTokens()[$next]['content']:NULL;
  }
}
