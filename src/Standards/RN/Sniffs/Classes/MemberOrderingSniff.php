<?php
declare(strict_types=1);
/**
 * requires PHP version 7.0+
 * @author Richard Nusser
 * @copyright 2017 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 */

namespace RN\CodeSnifferUtils\Sniffs\Classes;

use PHP_CodeSniffer\Sniffs\AbstractScopeSniff;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Exceptions\TokenizerException;
use RN\CodeSnifferUtils\Utils\TokenNames;

/**
 * Ensures class members are ordered correctly:
 *   1. consts
 *   2. static properties
 *   3. static methods
 *   4. instance properties
 *   5. instance methods
 */
class MemberOrderingSniff extends AbstractScopeSniff
{
  const ORDER_CONST=10;
  const ORDER_STATIC_PROPERTY=20;
  const ORDER_STATIC_METHOD=30;
  const ORDER_INSTANCE_PROPERTY=40;
  const ORDER_INSTANCE_METHOD=50;


  /**
   * Registers class parts to trigger on
   */
  public function __construct()
  {
    parent::__construct([T_CLASS],[T_CONST,T_VARIABLE,T_FUNCTION]);
  }

  /**
   * Processes the function tokens within the class.
   *
   * @param File $phpcsFile The file where this token was found.
   * @param int  $stackPtr  The position where the token was found.
   * @param int  $currScope (unused) The current scope opener token.
   * @return void
   */
  protected function processTokenWithinScope(File $phpcsFile, $stackPtr, $currScope)
  {
    $tokens=$phpcsFile->getTokens();
    $order=NULL;
    $name=NULL;
    $error_prefix=NULL;
    try
    {
      switch($tokens[$stackPtr]['code'])
      {
        case T_CONST:
          $order=self::ORDER_CONST;
          $name='constants';
          $error_prefix='Const';
          break;
        case T_VARIABLE:
          //skip method arguments
          if(!empty($tokens[$stackPtr]['nested_parenthesis']))
            return;

          if($phpcsFile->getMemberProperties($stackPtr)['is_static'])
          {
            $order=self::ORDER_STATIC_PROPERTY;
            $name='static properties';
            $error_prefix='StaticProperty';
          }
          else
          {
            $order=self::ORDER_INSTANCE_PROPERTY;
            $name='instance properties';
            $error_prefix='InstanceProperty';
          }
          break;
        case T_FUNCTION:
          if($phpcsFile->getMethodProperties($stackPtr)['is_static'])
          {
            $order=self::ORDER_STATIC_METHOD;
            $name='static methods';
            $error_prefix='StaticMethod';
          }
          else
          {
            $order=self::ORDER_INSTANCE_METHOD;
            $name='instance methods';
            $error_prefix='InstanceMethod';
          }
          break;
      }
    }
    catch(TokenizerException $e)
    {
      return;
    }

    if($order===NULL)
      throw new \LogicException("unhandled token: ".TokenNames::getPrintableName($tokens[$stackPtr]['code'],$tokens[$stackPtr]['type']));

    $error_description=$this->_checkTokenOrdering($phpcsFile,$stackPtr,$order);

    if($error_description)
      $phpcsFile->addError($name.' must be declared before any '.$error_description,$stackPtr,$error_prefix.'TooLate');
  }

  protected function _checkTokenOrdering(File $phpcsFile, int $stackPtr, int $order)
  {
    if($order<self::ORDER_STATIC_PROPERTY && $this->_hasPrecedingStaticProperty($phpcsFile,$stackPtr))
      return 'static properties';
    elseif($order<self::ORDER_STATIC_METHOD && $this->_hasPrecedingStaticMethod($phpcsFile,$stackPtr))
      return 'static methods';
    elseif($order<self::ORDER_INSTANCE_PROPERTY && $this->_hasPrecedingInstanceProperty($phpcsFile,$stackPtr))
      return 'instance properties';
    elseif($order<self::ORDER_INSTANCE_METHOD && $this->_hasPrecedingInstanceMethod($phpcsFile,$stackPtr))
      return 'instance methods';

    return NULL;
  }

  /**
   * Required by parent class
   *
   * @param File $phpcsFile (unused)
   * @param int  $stackPtr  (unused)
   * @return void
   */
  protected function processTokenOutsideScope(File $phpcsFile, $stackPtr)
  {
  }


  protected function _hasPrecedingMember(File $phpcsFile, $token, callable $member_check_func, bool $static, int $current): bool
  {
    $tokens=$phpcsFile->getTokens();
    while(true)
    {
      $current=$phpcsFile->findPrevious([T_CLASS,$token],$current-1,NULL,false);
      if($current===false)
        break;
      if($tokens[$current]['code']==T_CLASS)
        break;
      if(!empty($tokens[$current]['nested_parenthesis']))
        continue;
      try
      {
        if($member_check_func($current)['is_static']==$static)
          return true;
      }
      catch(TokenizerException $e)
      {
        1; //ignore: token wasn't a class member
      }
    }
    return false;
  }

  protected function _hasPrecedingStaticProperty(File $phpcsFile, int $current): bool
  {
    return $this->_hasPrecedingMember($phpcsFile,T_VARIABLE,[$phpcsFile,'getMemberProperties'],true,$current);
  }

  protected function _hasPrecedingStaticMethod(File $phpcsFile, int $current): bool
  {
    return $this->_hasPrecedingMember($phpcsFile,T_FUNCTION,[$phpcsFile,'getMethodProperties'],true,$current);
  }

  protected function _hasPrecedingInstanceProperty(File $phpcsFile, int $current): bool
  {
    return $this->_hasPrecedingMember($phpcsFile,T_VARIABLE,[$phpcsFile,'getMemberProperties'],false,$current);
  }

  protected function _hasPrecedingInstanceMethod(File $phpcsFile, int $current): bool
  {
    return $this->_hasPrecedingMember($phpcsFile,T_FUNCTION,[$phpcsFile,'getMethodProperties'],false,$current);
  }
}
