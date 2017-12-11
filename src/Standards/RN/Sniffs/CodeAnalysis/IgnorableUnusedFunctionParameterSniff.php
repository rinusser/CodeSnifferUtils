<?php
declare(strict_types=1);
/**
 * requires PHP version 7.0+
 * @author Richard Nusser
 * @copyright 2017 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 * @link https://github.com/rinusser/CodeSnifferUtils
 */

namespace RN\CodeSnifferUtils\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Standards\Generic\Sniffs\CodeAnalysis\UnusedFunctionParameterSniff;
use PHP_CodeSniffer\Files\File;
use RN\CodeSnifferUtils\Utils\IgnorableUnusedFunctionParameterFileProxy;
use RN\CodeSnifferUtils\Utils\PerFileSniffConfig;
use RN\CodeSnifferUtils\Utils\NoImplicitProperties;

/**
 * This is based on Generic.CodeAnalysis.UnusedFunctionParameterSniff, but minds knowingly unused function parameters
 */
class IgnorableUnusedFunctionParameterSniff extends UnusedFunctionParameterSniff
{
  use PerFileSniffConfig;

  //disallow access to undeclared properties
  use NoImplicitProperties;


  /**
   * Gets called by phpcs to handle a file's token
   *
   * @param File $file      the phpcs file handle
   * @param int  $stack_ptr the token offset to be processed
   * @return int|NULL an indicator for phpcs whether to process the rest of the file normally
   */
  public function process(File $file, $stack_ptr)
  {
    if($this->_isDisabledInFile($file))
      return $file->numTokens;

    $proxy=new IgnorableUnusedFunctionParameterFileProxy($file);
    return parent::process($proxy,$stack_ptr);
  }
}
