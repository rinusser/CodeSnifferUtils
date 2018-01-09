<?php
declare(strict_types=1);
/**
 * requires PHP version 7.1+
 * @author Richard Nusser
 * @copyright 2017-2018 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 * @link https://github.com/rinusser/CodeSnifferUtils
 */

namespace RN\CodeSnifferUtils\Standards\RN\Sniffs\Spacing;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;
use RN\CodeSnifferUtils\Utils\TokenNames;
use RN\CodeSnifferUtils\Config\PerFileSniffConfig;
use RN\CodeSnifferUtils\Utils\NoImplicitProperties;

/**
 * Ensures opening curly brackets are succeded by the proper amount of newlines: none.
 */
class OpeningBracketSniff implements Sniff
{
  use PerFileSniffConfig;

  //disallow access to undeclared properties
  use NoImplicitProperties;


  /**
   * Gets called by phpcs to return a list of tokens types to wait for
   *
   * @return array the list of token types
   */
  public function register()
  {
    return [T_OPEN_CURLY_BRACKET];
  }

  /**
   * Gets called by phpcs to handle a file's token
   *
   * @param File $file      the phpcs file handle
   * @param int  $stack_ptr the token offset to be processed
   * @return int|NULL an indicator for phpcs whether to process the rest of the file normally
   */
  public function process(File $file, $stack_ptr)
  {
    if($this->_isDisabledInFile($file))
      return $file->numTokens;

    $tokens=$file->getTokens();
    $next=$file->findNext(T_WHITESPACE,$stack_ptr+1,NULL,true);
    $lines_between=$tokens[$next]['line']-$tokens[$stack_ptr]['line']-1;
    if($lines_between>0)
    {
      $currents_name=TokenNames::getPrintableName($tokens[$stack_ptr]['code'],$tokens[$stack_ptr]['type']);
      $error='Expected 0 empty lines after '.$currents_name.', got '.$lines_between.' instead';
      $fix=$file->addFixableError($error,$stack_ptr,'SucceedingNewlines');
      if($fix)
      {
        $spacing=$file->getTokensAsString($stack_ptr+1,$next-$stack_ptr-1);
        $space_parts=explode("\n",$spacing);
        $file->fixer->beginChangeset();
        $file->fixer->replaceToken($stack_ptr+1,"\n".array_pop($space_parts));
        for($ti=$stack_ptr+2;$ti<$next;$ti++)
          $file->fixer->replaceToken($ti,'');
        $file->fixer->endChangeset();
      }
    }
    return NULL;
  }
}
