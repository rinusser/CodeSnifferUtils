<?php
declare(strict_types=1);
/**
 * requires PHP version 7.0+
 * @author Richard Nusser
 * @copyright 2017 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 */

//namespace RN\CodeSnifferUtils\Sniffs\Capitalization;
namespace PHP_CodeSniffer\Standards\RN\Sniffs\Capitalization; //phpcs property injection doesn't work otherwise

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Ensures true and false are lowercase, NULL is uppercase
 */
class BooleanNULLSniff implements Sniff
{
  public const UPPER='upper';
  public const UCFIRST='ucfirst';
  public const LOWER='lower';


  public $booleanCase=self::LOWER;
  public $nullCase=self::UPPER;


  /**
   * Returns list of phpcs hooks this sniff should be triggered on
   * Called by phpcs automatically.
   *
   * @return array
   */
  public function register()
  {
    $this->_validateCaseProperty($this->booleanCase,'booleanCase');
    $this->_validateCaseProperty($this->nullCase,'nullCase');

    return [T_TRUE,T_FALSE,T_NULL];
  }

  /**
   * @param File $file      the phpcs file handle to check
   * @param int  $stack_ptr the phpcs context
   * @return NULL to indicate phpcs should continue processing rest of file normally
   */
  public function process(File $file, $stack_ptr)
  {
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
