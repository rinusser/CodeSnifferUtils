<?php
declare(strict_types=1);
/**
 * requires PHP version 7.0+
 * @author Richard Nusser <do.not@con.tact>
 * @copyright 2017 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 */

namespace RN\CodeSnifferUtils\Sniffs\Files;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Util\Tokens;

/**
 * Ensures PHP files declare strict typing
 */
class DeclareStrictSniff implements Sniff
{
  /**
   * Returns list of phpcs hooks this sniff should be triggered on
   * Called by phpcs automatically.
   *
   * @return array
   */
  public function register()
  {
    return array(T_OPEN_TAG);
  }

  /**
   * @param File $phpcsFile the phpcs file handle to check
   * @param int  $stackPtr  the phpcs context
   * @return int the total number of tokens in the file, so phpcs continues other processing sniffs
   */
  public function process(File $phpcsFile, $stackPtr)
  {
    $tokens = $phpcsFile->getTokens();
    if($tokens[1]['code']!==T_DECLARE)
    {
      $error='Every PHP file should declare strict_types';
      $phpcsFile->addWarning($error,$stackPtr,'DeclareStrictMissing');
    }
    return $phpcsFile->numTokens;
  }
}
