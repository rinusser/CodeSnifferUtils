<?php
declare(strict_types=1);
/**
 * requires PHP version 7.0+
 * @author Richard Nusser
 * @copyright 2017 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 * @link https://github.com/rinusser/CodeSnifferUtils
 */

namespace RN\CodeSnifferUtils\Utils;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Exceptions\TokenizerException;

/**
 * Helper functions for analyzing/finding class members
 */
trait ClassMemberChecker
{
  protected function _hasPrecedingMember(File $file, $token, ?callable $member_check_func, int $current): bool
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
        if(!$member_check_func || $member_check_func($current))
          return true;
      }
      catch(TokenizerException $e)
      {
        continue; //ignore: token wasn't a class member
      }
    }
    return false;
  }

  private function _isStatic(callable $checker, bool $static): callable
  {
    return function(int $current) use ($checker,$static): bool {
      return $checker($current)['is_static']==$static;
    };
  }

  private function _isInstanceMethodXorConstructor(File $file, bool $constructor): callable
  {
    return function(int $current) use ($file,$constructor): bool {
      $checker=[$file,'getMethodProperties'];
      return $this->_isStatic($checker,false)($current) && ($this->_getMethodName($file,$current)==='__construct')===$constructor;
    };
  }

  private function _isTraitImport(File $file): callable
  {
    return function(int $current) use ($file): bool {
      $tokens=$file->getTokens();
      if($tokens[$current]['code']!==T_USE)
        return true;
      $next=$file->findNext([T_WHITESPACE],$current+1,NULL,true);
      return $next!==false && $tokens[$next]['code']!==T_OPEN_PARENTHESIS;
    };
  }

  protected function _hasPrecedingConst(File $file, int $current): bool
  {
    return $this->_hasPrecedingMember($file,T_CONST,NULL,$current);
  }

  protected function _hasPrecedingTraitUse(File $file, int $current): bool
  {
    return $this->_hasPrecedingMember($file,T_USE,$this->_isTraitImport($file),$current);
  }

  protected function _hasPrecedingStaticProperty(File $file, int $current): bool
  {
    return $this->_hasPrecedingMember($file,T_VARIABLE,$this->_isStatic([$file,'getMemberProperties'],true),$current);
  }

  protected function _hasPrecedingStaticMethod(File $file, int $current): bool
  {
    return $this->_hasPrecedingMember($file,T_FUNCTION,$this->_isStatic([$file,'getMethodProperties'],true),$current);
  }

  protected function _hasPrecedingInstanceProperty(File $file, int $current): bool
  {
    return $this->_hasPrecedingMember($file,T_VARIABLE,$this->_isStatic([$file,'getMemberProperties'],false),$current);
  }

  protected function _hasPrecedingConstructor(File $file, int $current): bool
  {
    return $this->_hasPrecedingMember($file,T_FUNCTION,$this->_isInstanceMethodXorConstructor($file,true),$current);
  }

  protected function _hasPrecedingInstanceMethod(File $file, int $current): bool
  {
    return $this->_hasPrecedingMember($file,T_FUNCTION,$this->_isInstanceMethodXorConstructor($file,false),$current);
  }


  private function _getMethodName(File $file, int $stack_ptr)
  {
    $next=$file->findNext([T_WHITESPACE],$stack_ptr+1,NULL,true);
    return $next!==false?$file->getTokens()[$next]['content']:NULL;
  }
}
