<?php
declare(strict_types=1);
/**
 * requires PHP version 7.1+
 * @author Richard Nusser
 * @copyright 2017 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 * @link https://github.com/rinusser/CodeSnifferUtils
 */

namespace RN\CodeSnifferUtils\Standards\RN\Sniffs\Spacing;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;
use RN\CodeSnifferUtils\Config\PerFileSniffConfig;
use RN\CodeSnifferUtils\Utils\NoImplicitProperties;

/**
 * Ensures closure's opening curly brackets are on same line as function keyword
 */
class ClosureOpeningBracketSniff implements Sniff
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
    return [T_CLOSURE];
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
    $closure=$tokens[$stack_ptr];
    $opener=$tokens[$closure['scope_opener']];

    if($opener['line']!=$closure['line'])
    {
      $error='Expected closure\'s opening curly bracket on same line as \'function\' keyword';
      $prev=$file->findPrevious([T_WHITESPACE],$closure['scope_opener']-1,NULL,true);
      if($tokens[$prev]['code']===T_CLOSE_PARENTHESIS && $tokens[$prev]['line']==$closure['line'])
      {
        if($file->addFixableError($error,$closure['scope_opener'],'PrecedingNewlines'))
        {
          $cur=$prev+1;
          $file->fixer->beginChangeset();
          $file->fixer->replaceToken($cur,' ');
          while(++$cur<$closure['scope_opener'])
            $file->fixer->replaceToken($cur,'');
          $file->fixer->endChangeset();
        }
      }
      else
        $file->addError($error,$closure['scope_opener'],'PrecedingNewlines');
    }
  }
}
