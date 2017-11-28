<?php
declare(strict_types=1);
/**
 * requires PHP version 7.0+
 * @author Richard Nusser
 * @copyright 2017 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 */

namespace RN\CodeSnifferUtils\Sniffs\Naming;

use PHP_CodeSniffer\Sniffs\AbstractVariableSniff;
use PHP_CodeSniffer\Files\File;

/**
 * Ensures properties are named properly:
 *  - public properties must not start with an underscore
 *  - private/protected properties must start with an underscore
 *  - after the leading underscore (or not), properties must start with a lowercase letter and not contain any other underscores
 */
class PropertySniff extends AbstractVariableSniff
{
  /**
   * @param File $phpcsFile the phpcs file handle to check
   * @param int  $stackPtr  the phpcs context
   * @return NULL to indicate phpcs should continue processing rest of file normally
   */
  protected function processMemberVar(File $phpcsFile, $stackPtr)
  {
    $tokens=$phpcsFile->getTokens();
    $full_name=$tokens[$stackPtr]['content'];
    $name=ltrim($full_name,'$');

    $properties=$phpcsFile->getMemberProperties($stackPtr);
    if(!$properties)
      return;

    $has_leading_underscore=$name[0]==='_';
    $should_have_leading_underscore=$properties['scope']!=='public';

    if($has_leading_underscore!==$should_have_leading_underscore)
    {
      $error=ucfirst($properties['scope']).' property "%s" should '.($should_have_leading_underscore?'':'not ').' start with an underscore';
      $code=$should_have_leading_underscore?'NonPublicUnderscoreMissing':'PublicHasUnderscore';
      $phpcsFile->addError($error,$stackPtr,$code,[$full_name]);
      return;
    }

    if($has_leading_underscore)
      $name=substr($name,1);

    if(!ctype_lower($name[0]))
    {
      $error='Property "%s" should start with a lowercase character';
      $phpcsFile->addError($error,$stackPtr,'InvalidStart',[$full_name]);
      return;
    }

    if(strpos($name,'_')!==false)
    {
      $error='Property "%s" should not contain underscores after the start of the name';
      $phpcsFile->addError($error,$stackPtr,'UnderscoreAfterStart',[$full_name]);
      return;
    }
  }

  protected function processVariable(File $phpcsFile, $stackPtr)
  {
  }

  protected function processVariableInString(File $phpcsFile, $stackPtr)
  {
  }
}
