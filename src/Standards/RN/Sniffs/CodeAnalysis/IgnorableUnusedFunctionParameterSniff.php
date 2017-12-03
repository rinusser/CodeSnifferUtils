<?php
declare(strict_types=1);
/**
 * requires PHP version 7.0+
 * @author Richard Nusser
 * @copyright 2017 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 */

namespace RN\CodeSnifferUtils\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Standards\Generic\Sniffs\CodeAnalysis\UnusedFunctionParameterSniff;
use PHP_CodeSniffer\Files\File;
use RN\CodeSnifferUtils\Utils\IgnorableUnusedFunctionParameterFileProxy;
use RN\CodeSnifferUtils\Utils\PerFileSniffConfig;

/**
 * This is based on Generic.CodeAnalysis.UnusedFunctionParameterSniff, but minds knowingly unused function parameters
 */
class IgnorableUnusedFunctionParameterSniff extends UnusedFunctionParameterSniff
{
  use PerFileSniffConfig;

  /**
   * @param File $file      the phpcs file handle to check
   * @param int  $stack_ptr the phpcs context
   * @return mixed see parent class
   */
  public function process(File $file, $stack_ptr)
  {
    if($this->_isDisabledInFile($file))
      return;

    $proxy=new IgnorableUnusedFunctionParameterFileProxy($file);
    return parent::process($proxy,$stack_ptr);
  }
}
