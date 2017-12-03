<?php
declare(strict_types=1);
/**
 * requires PHP version 7.0+
 * @author Richard Nusser
 * @copyright 2017 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 */

namespace RN\CodeSnifferUtils\Sniffs\Spacing;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;
use RN\CodeSnifferUtils\Utils\TokenNames;
use RN\CodeSnifferUtils\Utils\PerFileSniffConfig;

/**
 * Ensures opening curly brackets are succeded by the proper amount of newlines: none.
 */
class OpeningBracketSniff implements Sniff
{
  use PerFileSniffConfig;

  /**
   * Registers this sniff's triggers
   * @return array
   */
  public function register()
  {
    return [T_OPEN_CURLY_BRACKET];
  }

  /**
   * @param File $file      the phpcs file handle to check
   * @param int  $stack_ptr the phpcs context
   * @return NULL to indicate phpcs should continue processing rest of file normally
   */
  public function process(File $file, $stack_ptr)
  {
    if($this->_isDisabledInFile($file))
      return;

    $tokens=$file->getTokens();
    $next=$file->findNext(T_WHITESPACE,$stack_ptr+1,NULL,true);
    $lines_between=$tokens[$next]['line']-$tokens[$stack_ptr]['line']-1;
    if($lines_between>0)
    {
      $currents_name=TokenNames::getPrintableName($tokens[$stack_ptr]['code'],$tokens[$stack_ptr]['type']);
      $error='Expected 0 empty lines after '.$currents_name.', got '.$lines_between.' instead';
      $file->addError($error,$stack_ptr,'SucceedingNewlines');
    }
    return NULL;
  }
}
