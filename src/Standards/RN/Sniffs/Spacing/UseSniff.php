<?php
declare(strict_types=1);
/**
 * requires PHP version 7.0+
 * @author Richard Nusser
 * @copyright 2017 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 * @link https://github.com/rinusser/CodeSnifferUtils
 */

namespace RN\CodeSnifferUtils\Sniffs\Spacing;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;
use RN\CodeSnifferUtils\Utils\PrecedingEmptyLinesChecker;
use RN\CodeSnifferUtils\Utils\PerFileSniffConfig;

/**
 * Ensures use declarations are preceded by the proper amount of newlines
 */
class UseSniff implements Sniff
{
  use PerFileSniffConfig;

  /**
   * Returns list of phpcs hooks this sniff should be triggered on
   * Called by phpcs automatically.
   *
   * @return array
   */
  public function register()
  {
    return [T_USE];
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

    $allowed_by_type=[T_OPEN_TAG=>0,
                      T_DECLARE=>1,
                      T_NAMESPACE=>1,
                      T_USE=>0,
                      T_CLOSE_PARENTHESIS=>-1, //this is for lambda expressions
                      T_OPEN_CURLY_BRACKET=>0, //this is for trait imports
                      T_DOC_COMMENT_CLOSE_TAG=>1,
                      T_COMMENT=>[0,1]];
    return (new PrecedingEmptyLinesChecker())->process($file,$stack_ptr,$allowed_by_type);
  }
}
