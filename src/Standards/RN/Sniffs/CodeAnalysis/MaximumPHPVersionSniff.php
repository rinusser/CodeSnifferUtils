<?php
declare(strict_types=1);
/**
 * requires PHP version 7.1+
 * @author Richard Nusser
 * @copyright 2017 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 * @link https://github.com/rinusser/CodeSnifferUtils
 */

namespace RN\CodeSnifferUtils\Standards\RN\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use RN\CodeSnifferUtils\Config\PerFileSniffConfig;
use RN\CodeSnifferUtils\Utils\NoImplicitProperties;
use RN\CodeSnifferUtils\Checkers\FileVersionChecker;

/**
 * This sniff checks whether a file seems to exceed the configured maximum PHP version
 */
class MaximumPHPVersionSniff implements Sniff
{
  use PerFileSniffConfig;

  //disallow access to undeclared properties
  use NoImplicitProperties;


  public $maximumVersion;


  /**
   * Called by phpcs, this returns the list of tokens to listen for
   *
   * @return array
   */
  public function register()
  {
    if($this->maximumVersion===NULL || $this->maximumVersion==='')
      return [];
    if(!preg_match('/^[1-9][0-9a-z._-]*$/i',$this->maximumVersion))
      throw new \InvalidArgumentException('invalid PHP version configured: "'.$this->maximumVersion.'"');
    return [T_OPEN_TAG];
  }


  /**
   * Gets called by phpcs to handle a file's token
   *
   * @param File $file      the phpcs file handle
   * @param int  $stack_ptr the token offset to be processed
   * @return int the number of tokens in the file, indicating phpcs should skip the rest of the file
   */
  public function process(File $file, $stack_ptr)
  {
    if($this->_isDisabledInFile($file))
      return $file->numTokens;

    $found_version=FileVersionChecker::findVersion($file);
    if(version_compare($this->maximumVersion,$found_version)>=0)
      return $file->numTokens;

    $warning='File seems to use PHP features beyond configured maximum version: highest version allowed is %s but file requires %s';
    $file->addWarning($warning,$stack_ptr,'MaximumExceeded',[$this->maximumVersion,$found_version]);

    return $file->numTokens;
  }
}
