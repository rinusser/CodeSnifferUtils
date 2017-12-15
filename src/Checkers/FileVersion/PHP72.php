<?php
declare(strict_types=1);
/**
 * requires PHP version 7.1+
 * @author Richard Nusser
 * @copyright 2017 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 * @link https://github.com/rinusser/CodeSnifferUtils
 */

namespace RN\CodeSnifferUtils\Checkers\FileVersion;

use PHP_CodeSniffer\Files\File;

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
   *
   * @param File $file the phpcs file handle to check
   * @return bool true if any uses were found
   */
  public function usesObjectTypeHint(File $file): bool
  {
    $cur=0;
    while(true)
    {
      $cur=$file->findNext([T_FUNCTION,T_CLOSURE],$cur+1);
      if($cur===false)
        break;
      foreach($file->getMethodParameters($cur) as $properties)
        if(!empty($properties['type_hint']) && $properties['type_hint']==='object')
          return true;
    }
    return false;
  }
}
