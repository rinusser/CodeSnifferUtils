<?php
declare(strict_types=1);
/**
 * requires PHP version 7.0+
 * @author Richard Nusser <do.not@con.tact>
 * @copyright 2017 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 */

namespace RN\CodeSnifferUtils\Sniffs\Spacing;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Util\Tokens;
use RN\CodeSnifferUtils\Utils\ContextAwarePrecedingEmptyLinesChecker;

/**
 * Ensures class declarations are preceded by the proper amount of newlines
 */
class ClassSniff implements Sniff
{
  /**
   * Gets called by phpcs to register what tokens to trigger on
   *
   * @return array the list of tokens
   */
  public function register()
  {
    return [T_CLASS];
  }

  /**
   * Gets called by phpcs to handle a file's token
   *
   * @param File $phpcsFile the phpcs file handle to check
   * @param int  $stackPtr  the phpcs context
   * @return NULL to indicate phpcs should continue processing rest of file normally
   */
  public function process(File $phpcsFile, $stackPtr)
  {
    $allowed_by_type=[T_CLOSE_CURLY_BRACKET=>[1,2],
                      T_SEMICOLON=>[1,2],
                      T_COMMENT=>[0,2],
                      T_DOC_COMMENT_CLOSE_TAG=>0,
                      T_OPEN_TAG=>[0,2]];
    return (new ContextAwarePrecedingEmptyLinesChecker(T_CLASS,[T_ABSTRACT]))->process($phpcsFile,$stackPtr,$allowed_by_type);
  }
}
