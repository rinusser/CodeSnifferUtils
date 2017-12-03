<?php
declare(strict_types=1);
/**
 * requires PHP version 7.0+
 * @author Richard Nusser
 * @copyright 2017 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 */

namespace RN\CodeSnifferUtils\Sniffs\Spacing;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;
use RN\CodeSnifferUtils\Utils\TokenNames;
use RN\CodeSnifferUtils\Utils\PerFileSniffConfig;

/**
 * Ensures function/method parameters are spaced properly
 */
class FunctionParametersSniff implements Sniff
{
  use PerFileSniffConfig;

  /**
   * Gets called by phpcs to register what tokens to trigger on
   *
   * @return array the list of tokens
   */
  public function register()
  {
    return [T_FUNCTION];
  }

  /**
   * Gets called by phpcs to handle a file's token
   *
   * @param File $file      the phpcs file handle to check
   * @param int  $stack_ptr the phpcs context
   * @return NULL to indicate phpcs should continue processing rest of file normally
   */
  public function process(File $file, $stack_ptr)
  {
    if($this->_isDisabledInFile($file))
      return;

    $tokens=$file->getTokens();
    if(empty($tokens[$stack_ptr]['parenthesis_opener']) || empty($tokens[$stack_ptr]['parenthesis_closer']))
    {
      $warning='cannot parse function declaration: parentheses incomplete';
      $file->addWarning($warning,$stack_ptr,'IncompleteDeclaration');
      return;
    }
    $beginning=$tokens[$stack_ptr]['parenthesis_opener'];
    $end=$tokens[$stack_ptr]['parenthesis_closer'];
    if($end<=$beginning+1)
      return;

    $current=$beginning;
    while(true)
    {
      $current=$file->findNext(T_VARIABLE,$current+1,NULL,false,NULL,true);
      if($current===false || $current>=$end)
        break;

      $type=$file->findPrevious([T_EQUAL,T_COMMA,T_OPEN_PARENTHESIS],$current,NULL,false,NULL,true);
      if($type===false)
      {
        $warning='Could not parse parameter context';
        $file->addWarning($warning,$current,'CouldNotParseContext');
        return;
      }

      $preceding_tokens=[T_EQUAL=>['','default value','Equals'],
                         T_COMMA=>[' ','function parameter','Comma'],
                         T_OPEN_PARENTHESIS=>['','function parameter','OpeningParenthesis']];
      $preceding_token=$preceding_tokens[$tokens[$type]['code']];

      $str=$file->getTokensAsString($type+1,$current-$type-1);
      $str=$this->_stripParameterModifiers($str);

      if($tokens[$type]['code']!==T_EQUAL && trim($str))
        $str=$this->_processTypeHint($str,$file,$current);

      if(!trim($str) && strpos($str,"\n")!==false)
        continue;

      if($str!==$preceding_token[0])
      {
        $error='Wrong distance between '.TokenNames::getPrintableName($tokens[$type]).' and '.$preceding_token[1];
        $file->addError($error,$current,'WrongSpaceAfter'.$preceding_token[2]);
      }
    }
  }

  protected function _stripParameterModifiers(string $str): string
  {
    if(strlen($str)>0 && $str[-1]==='&')
      $str=substr($str,0,-1);
    if(strlen($str)>=3 && substr($str,-3)==='...')
      $str=substr($str,0,-3);
    return $str;
  }

  protected function _processTypeHint(string $str, File $file, int $current): string
  {
    if(preg_match('/[ \t]/',trim($str)))
    {
      $warning='Could not parse type hint "'.$str.'", it contains whitespaces';
      $file->addWarning($warning,$current-1,'CantParseTypeHint');
      return '';
    }

    if(strlen($str)>=2)
    {
      $last2=substr($str,-2);
      if($last2[0]===' ' || $last2[1]!==' ')
      {
        $error='Wrong distance after type hint in function parameter, expected 1 blank';
        $file->addError($error,$current-1,'WrongSpaceAfterTypeHint');
      }
      $str=rtrim($str);
    }
    if(preg_match('/^([ \t\r\n]*)(\\??[\\\\A-Z0-9_]+)$/i',$str,$matches))
      $str=$matches[1];

    return $str;
  }
}
