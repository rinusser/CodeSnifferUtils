<?php
declare(strict_types=1);
/**
 * requires PHP version 7.0+
 * @author Richard Nusser
 * @copyright 2017 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 */

namespace RN\CodeSnifferUtils\Sniffs\Classes;

use PHP_CodeSniffer\Sniffs\AbstractScopeSniff;
use PHP_CodeSniffer\Files\File;
use RN\CodeSnifferUtils\Utils\PerFileSniffConfig;
use RN\CodeSnifferUtils\Utils\FileUtils;

/**
 * Ensures class constants have an explicit visibility set
 */
class ExplicitConstVisibilitySniff extends AbstractScopeSniff
{
  use PerFileSniffConfig;


  /**
   * Registers class parts to trigger on
   */
  public function __construct()
  {
    parent::__construct([T_CLASS],[T_CONST]);
  }


  /**
   * Processes the function tokens within the class.
   *
   * @param File $file       The file where this token was found.
   * @param int  $stack_ptr  The position where the token was found.
   * @param int  $curr_scope (unused) The current scope opener token.
   * @return void
   */
  protected function processTokenWithinScope(File $file, $stack_ptr, $curr_scope)  //CSU.IgnoreName: required by parent class
  {
    if($this->_isDisabledInFile($file) || FileUtils::getTargetPHPVersion()<70100)
      return $file->numTokens;

    $properties=FileUtils::getConstProperties($file,$stack_ptr);
    if($properties['scope_specified'])
      return;

    $error='Constants must have an explicit visibility set';
    $file->addError($error,$stack_ptr,'ImplicitVisibility');
  }


  /**
   * Required by parent class
   *
   * @param File $file      (unused)
   * @param int  $stack_ptr (unused)
   * @return void
   */
  protected function processTokenOutsideScope(File $file, $stack_ptr)  //CSU.IgnoreName: required by parent class
  {
  }
}
