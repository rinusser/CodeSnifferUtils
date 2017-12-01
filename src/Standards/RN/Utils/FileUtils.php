<?php
declare(strict_types=1);
/**
 * requires PHP version 7.0+
 * @author Richard Nusser
 * @copyright 2017 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 */

namespace RN\CodeSnifferUtils\Utils;

use PHP_CodeSniffer\Files\File;

/**
 * Class supplying phpcs file helpers
 */
abstract class FileUtils
{
  /**
   * Finds tokens on the same line not in list of token types
   *
   * @param File  $file     the phpcsfile handle
   * @param array $excludes the token types to exclude
   * @param int   $start    the reference token to look before of
   * @return int|bool the previous token offset in same line not in $excludes, or false if none found
   */
  public static function findPreviousOnLineExcept(File $file, array $excludes, int $start)
  {
    $tokens=$file->getTokens();
    $start_line=$tokens[$start]['line'];
    $cur=$start-1;
    while($cur>=0 && $tokens[$cur]['line']==$start_line)
    {
      if(!in_array($tokens[$cur]['code'],$excludes))
        return $cur;
      $cur--;
    }
    return false;
  }
}
