<?php
declare(strict_types=1);
/**
 * requires PHP version 7.0+
 * @author Richard Nusser
 * @copyright 2017 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 * @link https://github.com/rinusser/CodeSnifferUtils
 */

namespace RN\CodeSnifferUtils\Sniffs\Classes;

use PHP_CodeSniffer\Sniffs\AbstractVariableSniff;
use PHP_CodeSniffer\Files\File;
use RN\CodeSnifferUtils\Utils\PerFileSniffConfig;

/**
 * Ensures class properties are declared individually
 */
class IndividualPropertiesSniff extends AbstractVariableSniff
{
  use PerFileSniffConfig;

  /**
   * Processes the property tokens within the class.
   *
   * @param File $file      The file where this token was found.
   * @param int  $stack_ptr The position where the token was found.
   * @return void
   */
  protected function processMemberVar(File $file, $stack_ptr)  //CSU.IgnoreName: required by parent class
  {
    if($this->_isDisabledInFile($file))
      return;

    $tokens=$file->getTokens();
    $prev=$file->findPrevious([T_WHITESPACE,T_COMMENT],$stack_ptr-1,NULL,true);
    if($tokens[$prev]['code']!==T_COMMA)
      return;

    $warning='Multiple properties in one statement; properties should be declared individually instead';
    $file->addWarning($warning,$stack_ptr,'MultipleFound');
  }


  protected function processVariable(File $file, $stack_ptr)  //CSU.IgnoreName: required by parent class
  {
  }

  protected function processVariableInString(File $file, $stack_ptr)  //CSU.IgnoreName: required by parent class
  {
  }
}
