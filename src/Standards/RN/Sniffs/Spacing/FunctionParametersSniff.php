<?php
declare(strict_types=1);
/**
 * requires PHP version 7.1+
 * @author Richard Nusser
 * @copyright 2017-2018 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 * @link https://github.com/rinusser/CodeSnifferUtils
 */

namespace RN\CodeSnifferUtils\Standards\RN\Sniffs\Spacing;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;
use RN\CodeSnifferUtils\Utils\TokenNames;
use RN\CodeSnifferUtils\Config\PerFileSniffConfig;
use RN\CodeSnifferUtils\Utils\NoImplicitProperties;

/**
 * Ensures function/method parameters are spaced properly
 */
class FunctionParametersSniff implements Sniff
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
    return [T_FUNCTION];
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
        $str=$this->_processTypeHint($str,$file,$current,$type+1);

      if(!trim($str) && strpos($str,"\n")!==false)
        continue;

      if($str!==$preceding_token[0])
        $this->_handleLeftError($file,$type,$current,$preceding_token);
    }
  }

  private function _handleLeftError(File $file, int $type, int $current, array $preceding_token): void
  {
    $tokens=$file->getTokens();
    $error='Wrong distance between '.TokenNames::getPrintableName($tokens[$type]).' and '.$preceding_token[1];

    if(!$file->addFixableError($error,$current,'WrongSpaceAfter'.$preceding_token[2]))
      return;

    $file->fixer->beginChangeset();
    if($preceding_token[0]!=='' && $tokens[$type+1]['code']!==T_WHITESPACE)
    {
      $file->fixer->addContent($type,$preceding_token[0]);
    }
    else
    {
      for($ti=$type+1;$ti<$current;$ti++)
      {
        if($tokens[$ti]['code']!==T_WHITESPACE)
          break;
        $file->fixer->replaceToken($ti,$ti>$type+1?'':$preceding_token[0]);
      }
    }
    $file->fixer->endChangeset();
  }

  protected function _stripParameterModifiers(string $str): string
  {
    if(strlen($str)>0 && $str[-1]==='&')
      $str=substr($str,0,-1);
    if(strlen($str)>=3 && substr($str,-3)==='...')
      $str=substr($str,0,-3);
    return $str;
  }

  protected function _processTypeHint(string $str, File $file, int $current, int $start): string
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
        if($file->addFixableError($error,$current-1,'WrongSpaceAfterTypeHint'))
        {
          $hint_start=$file->findNext(T_WHITESPACE,$start,NULL,true);
          $first_space_after=$file->findNext(T_WHITESPACE,$hint_start+1);
          $file->fixer->beginChangeset();
          $file->fixer->replaceToken($first_space_after,' ');
          for($ti=$first_space_after+1;$ti<$current;$ti++)
          {
            if($tokens[$ti]['code']!==T_WHITESPACE)
              break;
            $file->fixer->replaceToken($ti,'');
          }
          $file->fixer->endChangeset();
        }
      }
      $str=rtrim($str);
    }
    if(preg_match('/^([ \t\r\n]*)(\\??[\\\\A-Z0-9_]+)$/i',$str,$matches))
      $str=$matches[1];

    return $str;
  }
}
