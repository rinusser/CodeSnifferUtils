<?php
declare(strict_types=1);
/**
 * requires PHP version 7.0+
 * @author Richard Nusser
 * @copyright 2017 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 */

namespace RN\CodeSnifferUtils\Sniffs\Files;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Util\Tokens;
use RN\CodeSnifferUtils\Utils\PerFileSniffConfig;
use RN\CodeSnifferUtils\Utils\FileUtils;

/**
 * Ensures PHP files declare strict typing
 */
class DeclareStrictSniff implements Sniff
{
  use PerFileSniffConfig;

  /**
   * Returns list of phpcs hooks this sniff should be triggered on
   * Called by phpcs automatically.
   *
   * @return array
   */
  public function register()
  {
    return array(T_OPEN_TAG);
  }

  /**
   * @param File $file      the phpcs file handle to check
   * @param int  $stack_ptr the phpcs context
   * @return int the total number of tokens in the file, so phpcs continues other processing sniffs
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
