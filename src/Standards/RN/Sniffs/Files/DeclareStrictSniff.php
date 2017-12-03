<?php
declare(strict_types=1);
/**
 * requires PHP version 7.0+
 * @author Richard Nusser
 * @copyright 2017 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 */

namespace RN\CodeSnifferUtils\Sniffs\Files;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;
use RN\CodeSnifferUtils\Utils\PerFileSniffConfig;

/**
 * Ensures PHP files declare strict typing
 */
class DeclareStrictSniff implements Sniff
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
    return array(T_OPEN_TAG);
  }

  /**
   * @param File $file      the phpcs file handle to check
   * @param int  $stack_ptr the phpcs context
   * @return int the total number of tokens in the file, so phpcs continues other processing sniffs
   */
  public function process(File $file, $stack_ptr)
  {
    if($this->_isDisabledInFile($file))
      return;

    $tokens = $file->getTokens();
    if($tokens[1]['code']!==T_DECLARE)
    {
      $error='Every PHP file should declare strict_types';
      $file->addWarning($error,$stack_ptr,'DeclareStrictMissing');
    }
    return $file->numTokens;
  }
}
