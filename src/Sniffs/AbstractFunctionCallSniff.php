<?php
declare(strict_types=1);
/**
 * requires PHP version 7.1+
 * @author Richard Nusser
 * @copyright 2017 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 * @link https://github.com/rinusser/CodeSnifferUtils
 */

namespace RN\CodeSnifferUtils\Sniffs;

use PHP_CodeSniffer\Files\File;
use RN\CodeSnifferUtils\Utils\NoImplicitProperties;
use RN\CodeSnifferUtils\Config\PerFileSniffConfig;
use RN\CodeSnifferUtils\Files\FileUtils;

/**
 * Base sniff for checking function calls
 */
abstract class AbstractFunctionCallSniff
{
  //allow sniff configuration in file
  use PerFileSniffConfig;

  //disallow access to undeclared properties
  use NoImplicitProperties;


  /**
   * Gets called by phpcs to return a list of tokens types to wait for
   *
   * @return array the list of token types
   */
  final public function register()
  {
    return [T_OPEN_PARENTHESIS];
  }

  /**
   * Gets called by phpcs to handle a file's token
   *
   * @param File $file      the phpcs file handle
   * @param int  $stack_ptr the token offset to be processed
   * @return int|NULL an indicator for phpcs whether to process the rest of the file normally
   */
  final public function process(File $file, $stack_ptr)
  {
    if($this->_isDisabledInFile($file))
      return $file->numTokens;

    $last_callee_token=FileUtils::findLastCalleeToken($file,$stack_ptr);
    if($last_callee_token!==false)
      return $this->_processCall($file,$last_callee_token,$stack_ptr);
  }


  /**
   * This method will be called for every function call found
   * For example:
   *
   *   $x->f1($a,$b);
   *       | ^ $parenthesis_opener
   *       ^ $last_callee_token
   *
   * @param File $file              the phpcs file handle to process
   * @param int $last_callee_token  the last token offset of the called function
   * @param int $parenthesis_opener the opening parenthesis's token offset
   * @return mixed see Sniff::process()
   */
  abstract protected function _processCall(File $file, int $last_callee_token, int $parenthesis_opener);
}
