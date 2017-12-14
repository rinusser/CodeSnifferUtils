<?php
declare(strict_types=1);
/**
 * requires PHP version 7.0+
 * @author Richard Nusser
 * @copyright 2017 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 * @link https://github.com/rinusser/CodeSnifferUtils
 */

namespace RN\CodeSnifferUtils\Standards\RN\Sniffs\Files;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Util\Tokens;
use RN\CodeSnifferUtils\Config\PerFileSniffConfig;
use RN\CodeSnifferUtils\Files\FileUtils;
use RN\CodeSnifferUtils\Utils\NoImplicitProperties;

/**
 * Ensures PHP files declare strict typing
 */
class DeclareStrictSniff implements Sniff
{
  use PerFileSniffConfig;

  //disallow access to undeclared properties
  use NoImplicitProperties;


  /**
   * Gets called by phpcs to return a list of tokens types to wait for
   *
   * @return array the list of token types
   */
  public function register()
  {
    return array(T_OPEN_TAG);
  }

  /**
   * Gets called by phpcs to handle a file's token
   *
   * @param File $file      the phpcs file handle
   * @param int  $stack_ptr the token offset to be processed
   * @return int|NULL an indicator for phpcs whether to process the rest of the file normally
   */
  public function process(File $file, $stack_ptr)
  {
    if($this->_isDisabledInFile($file) || FileUtils::getTargetPHPVersion()<70000)
      return $file->numTokens;

    $tokens=$file->getTokens();
    $code_start=$file->findNext(Tokens::$emptyTokens,$stack_ptr+1,NULL,true);

    if($code_start===false)
      return;

    if(!$this->_isDeclareStrict($file,$code_start))
    {
      $warning='Every PHP file should declare strict_types';
      $file->addWarning($warning,$stack_ptr,'Missing');
    }
    elseif(($tokens[$code_start]['line']-$tokens[$stack_ptr]['line'])!=1)
    {
      $error='Strict declaration should be on the line after the opening PHP tag';
      $file->addError($error,$code_start,'Misplaced');
    }

    return $file->numTokens;
  }

  private function _isDeclareStrict(File $file, int $offset): bool
  {
    $tokens=$file->getTokens();
    $token=$tokens[$offset];
    if($token['code']!==T_DECLARE || empty($token['parenthesis_opener']) || empty($token['parenthesis_closer']))
      return false;
    $str=$file->getTokensAsString($token['parenthesis_opener']+1,$token['parenthesis_closer']-$token['parenthesis_opener']-1);
    return (bool)preg_match('/^strict_types *= *1$/',trim($str));
  }
}
