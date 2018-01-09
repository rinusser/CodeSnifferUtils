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

/**
 * Base class for file version checks
 */
abstract class AbstractVersion
{
  /**
   * Checks whether a file uses features of the current check's language version
   *
   * @param File $file the phpcs file handle to check
   * @return bool whether the given file uses features from the current language version
   */
  final public function usesFeatures(File $file): bool
  {
    foreach(get_class_methods($this) as $method)
      if($method!=='usesFeatures' && substr(ltrim($method,'_'),0,4)==='uses' && $this->$method($file))
        return true;
    return false;
  }

  /**
   * Implement this method to indicate what version you're checking
   *
   * @return string the language version, e.g. "7.0"
   */
  abstract public function getVersion(): string;
}
