<?php
declare(strict_types=1);
/**
 * requires PHP version 7.0+
 * @author Richard Nusser
 * @copyright 2017 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 */

namespace RN\CodeSnifferUtils\Sniffs\Classes;

use PHP_CodeSniffer\Files\File;
use RN\CodeSnifferUtils\Utils\PerFileSniffConfig;
use PHP_CodeSniffer\Standards\PSR1\Sniffs\Classes\ClassDeclarationSniff as PSR1ClassDeclarationSniff;

/**
 * This equals PSR1.Classes.ClassDeclaration, but can be disabled for individual files
 */
class ClassDeclarationSniff extends PSR1ClassDeclarationSniff
{
  use PerFileSniffConfig;

  /**
   * Process a token
   * @param File $file      the phpcs file handle
   * @param int  $stack_ptr the token offset to process
   * @return mixed see parent class
   */
  public function process(File $file, $stack_ptr)
  {
    if($this->_isDisabledInFile($file))
      return $file->numTokens;

    return parent::process($file,$stack_ptr);
  }
}
