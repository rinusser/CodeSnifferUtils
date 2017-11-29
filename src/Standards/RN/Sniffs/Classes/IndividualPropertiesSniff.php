<?php
declare(strict_types=1);
/**
 * requires PHP version 7.0+
 * @author Richard Nusser
 * @copyright 2017 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 */

namespace RN\CodeSnifferUtils\Sniffs\Classes;

use PHP_CodeSniffer\Sniffs\AbstractVariableSniff;
use PHP_CodeSniffer\Files\File;

/**
 * Ensures class properties are declared individually
 */
class IndividualPropertiesSniff extends AbstractVariableSniff
{
  /**
   * Processes the property tokens within the class.
   *
   * @param File $phpcsFile The file where this token was found.
   * @param int  $stackPtr  The position where the token was found.
   * @return void
   */
  protected function processMemberVar(File $phpcsFile, $stackPtr)  //CSU.IgnoreName: required by parent class
  {
    $tokens=$phpcsFile->getTokens();
    $prev=$phpcsFile->findPrevious([T_WHITESPACE,T_COMMENT],$stackPtr-1,NULL,true);
    if($tokens[$prev]['code']!==T_COMMA)
      return;

    $warning='Multiple properties in one statement; properties should be declared individually instead';
    $phpcsFile->addWarning($warning,$stackPtr,'MultipleFound');
  }


  protected function processVariable(File $phpcsFile, $stackPtr)  //CSU.IgnoreName: required by parent class
  {
  }

  protected function processVariableInString(File $phpcsFile, $stackPtr)  //CSU.IgnoreName: required by parent class
  {
  }
}
