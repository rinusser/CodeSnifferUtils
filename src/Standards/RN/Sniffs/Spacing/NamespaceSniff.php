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
use RN\CodeSnifferUtils\Utils\PrecedingEmptyLinesChecker;

/**
 * Ensures namespace declaration is preceded by the proper amount of newlines
 */
class NamespaceSniff implements Sniff
{
  /**
   * Returns list of phpcs hooks this sniff should be triggered on
   * Called by phpcs automatically.
   *
   * @return array
   */
  public function register()
  {
    return [T_NAMESPACE];
  }

  /**
   * @param File $phpcsFile the phpcs file handle to check
   * @param int  $stackPtr  the phpcs context
   * @return NULL to indicate phpcs should continue processing rest of file normally
   */
  public function process(File $phpcsFile, $stackPtr)
  {
    $allowed_by_type=[T_OPEN_TAG=>0,
                      T_DECLARE=>0,
                      T_DOC_COMMENT_CLOSE_TAG=>1,
                      T_COMMENT=>[0,1]];
    return (new PrecedingEmptyLinesChecker())->process($phpcsFile,$stackPtr,$allowed_by_type);
  }
}
