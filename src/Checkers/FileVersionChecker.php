<?php
declare(strict_types=1);
/**
 * requires PHP version 7.1+
 * @author Richard Nusser
 * @copyright 2017-2018 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 * @link https://github.com/rinusser/CodeSnifferUtils
 */

namespace RN\CodeSnifferUtils\Checkers;

use PHP_CodeSniffer\Files\File;

/**
 * This class guesses a file's required PHP version
 */
abstract class FileVersionChecker
{
  private static $_checkers=[];

  private static function _init(): void
  {
    if(self::$_checkers)
      return;

    $checkers=[];
    foreach(glob(__DIR__.'/FileVersion/PHP*.php') as $file)
    {
      $class=__NAMESPACE__.'\\FileVersion\\'.substr(basename($file),0,-4);
      $checker=new $class();
      $version=$checker->getVersion();
      if(isset($checkers[$version]))
        throw new \LogicException('version "'.$version.'" is already registered');
      $checkers[$version]=$checker;
    }
    uksort($checkers,'version_compare');
    self::$_checkers=array_reverse($checkers);
  }

  /**
   * Returns the list of checked PHP versions
   *
   * @return array the list of PHP versions, e.g. ['7.2','7.1']
   */
  public static function getCheckedVersions(): array
  {
    self::_init();
    return array_keys(self::$_checkers);
  }

  /**
   * Attempts to determine a file's required PHP version
   *
   * @param File $file the phpcs file handle to check
   * @return string the PHP version, e.g. "7.1"
   */
  public static function findVersion(File $file): string
  {
    self::_init();
    foreach(self::$_checkers as $version=>$checker)
      if($checker->usesFeatures($file))
        return $version;
    return '5.6';
  }
}
