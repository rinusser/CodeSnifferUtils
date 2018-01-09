<?php
declare(strict_types=1);
/**
 * requires PHP version 7.1+
 * @author Richard Nusser
 * @copyright 2017-2018 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 * @link https://github.com/rinusser/CodeSnifferUtils
 */

namespace RN\CodeSnifferUtils\Standards\RN\Sniffs\Capitalization;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;
use RN\CodeSnifferUtils\Config\PerFileSniffConfig;
use RN\CodeSnifferUtils\Utils\NoImplicitProperties;

/**
 * Ensures true and false are lowercase, NULL is uppercase
 */
class BooleanNULLSniff implements Sniff
{
  public const UPPER='upper';
  public const UCFIRST='ucfirst';
  public const LOWER='lower';


  //import per-file config
  use PerFileSniffConfig;

  //disallow access to undeclared properties
  use NoImplicitProperties;


  public $booleanCase=self::LOWER;
  public $nullCase=self::UPPER;


  /**
   * Gets called by phpcs to return a list of tokens types to wait for
   *
   * @return array the list of token types
   */
  public function register()
  {
    $this->_validateCaseProperty($this->booleanCase,'booleanCase');
    $this->_validateCaseProperty($this->nullCase,'nullCase');

    return [T_TRUE,T_FALSE,T_NULL];
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
    if($this->_isDisabledInFile($file))
      return $file->numTokens;

    $tokens=$file->getTokens();
    $actual=$tokens[$stack_ptr]['content'];
    $expected=strtolower($actual);
    if($expected==='null')
    {
      $type='Null';
      $expected=$this->_convertCase($expected,$this->nullCase);
    }
    else
    {
      $type='Boolean';
      $expected=$this->_convertCase($expected,$this->booleanCase);
    }

    if($actual!==$expected)
    {
      $error='Invalid boolean/null value: expected "%s", got "%s" instead.';
      $fix=$file->addFixableError($error,$stack_ptr,$type.'Case',[$expected,$actual]);
      if($fix)
        $file->fixer->replaceToken($stack_ptr,$expected);
    }
  }

  private function _convertCase(string $value, string $case): string
  {
    switch($case)
    {
      case self::UPPER:
        return strtoupper($value);
      case self::UCFIRST:
        return ucfirst($value);
      case self::LOWER:
        return $value;
      default:
        throw new \LogicException('unhandled case');
    }
  }

  private function _validateCaseProperty($value, string $name): void
  {
    $allowed=[self::UPPER,self::UCFIRST,self::LOWER];
    if(is_string($value) && in_array($value,$allowed))
      return;
    throw new \InvalidArgumentException('invalid setting for property '.$name.': expected one of '.implode('|',$allowed).', got "'.$value.'" instead');
  }
}
