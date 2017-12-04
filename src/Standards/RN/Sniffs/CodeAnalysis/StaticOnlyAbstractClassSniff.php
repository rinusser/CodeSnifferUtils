<?php
declare(strict_types=1);
/**
 * requires PHP version 7.0+
 * @author Richard Nusser
 * @copyright 2017 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 */

namespace RN\CodeSnifferUtils\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;
use RN\CodeSnifferUtils\Utils\PerFileSniffConfig;
use RN\CodeSnifferUtils\Utils\ClassMemberChecker;

/**
 * Ensures classes containing only static members are abstract
 */
class StaticOnlyAbstractClassSniff implements Sniff
{
  use PerFileSniffConfig;
  use ClassMemberChecker;

  /**
   * Registers class parts to trigger on
   *
   * @return array the list of tokens
   */
  public function register()
  {
    return [T_CLASS];
  }

  /**
   * Processes the function tokens within the class.
   *
   * @param File $file      The file where this token was found.
   * @param int  $stack_ptr The position where the token was found.
   * @return void
   */
  public function process(File $file, $stack_ptr)
  {
    if($this->_isDisabledInFile($file))
      return;

    $tokens=$file->getTokens();

    $properties=$file->getClassProperties($stack_ptr);
    if($properties['is_abstract'])
      return;

    //just to be sure, might be useful for "A::class" statements
    if(empty($tokens[$stack_ptr]['scope_closer']))
      return;

    //skip classes extending other classes
    if($file->findNext(T_EXTENDS,$stack_ptr,$tokens[$stack_ptr]['scope_opener'],false)!==false)
      return;

    $end=$tokens[$stack_ptr]['scope_closer'];
    if($this->_hasPrecedingTraitUse($file,$end) || $this->_hasPrecedingInstanceProperty($file,$end) || $this->_hasPrecedingInstanceMethod($file,$end))
      return;

    if($this->_hasPrecedingStaticProperty($file,$end) || $this->_hasPrecedingStaticMethod($file,$end))
    {
      $warning='Class %s consists of static members only, should be abstract';
      $file->addWarning($warning,$stack_ptr,'ClassShouldBeAbstract',$file->getDeclarationName($stack_ptr));
    }
  }
}
