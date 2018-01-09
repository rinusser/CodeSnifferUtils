<?php
declare(strict_types=1);
/**
 * requires PHP version 7.1+
 * @author Richard Nusser
 * @copyright 2017-2018 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 * @link https://github.com/rinusser/CodeSnifferUtils
 */

namespace RN\CodeSnifferUtils\Checkers\FileVersion;

use PHP_CodeSniffer\Files\File;
use RN\CodeSnifferUtils\Files\FileUtils;

/**
 * File version checks for PHP 7.2
 */
class PHP72 extends AbstractVersion
{
  /**
   * Gets called automatically to determine what PHP version we're checking in this class
   *
   * @return string the PHP version
   */
  public function getVersion(): string
  {
    return '7.2';
  }

  /**
   * Checks whether the passed file uses PHP 7.2's "object" type hint
   * For example:
   *
   *   function asdf(object $x) {}
   *
   * @param File $file the phpcs file handle to check
   * @return bool true if any uses were found
   */
  public function usesObjectTypeHint(File $file): bool
  {
    foreach(FileUtils::findAllByTypes($file,[T_FUNCTION,T_CLOSURE]) as $current)
      foreach($file->getMethodParameters($current) as $properties)
        if(strtolower(ltrim($properties['type_hint'],'?'))==='object')
          return true;
    return false;
  }
}
