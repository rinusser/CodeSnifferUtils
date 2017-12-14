<?php
declare(strict_types=1);
/**
 * requires PHP version 7.1+
 * @author Richard Nusser
 * @copyright 2017 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 * @link https://github.com/rinusser/CodeSnifferUtils
 */

namespace RN\CodeSnifferUtils\Standards\RN\Sniffs\Spacing;

use PHP_CodeSniffer\Files\File;
use RN\CodeSnifferUtils\Sniffs\AbstractFunctionCallSniff;

/**
 * Ensures function call parameters are spaced properly
 */
class FunctionCallParametersSniff extends AbstractFunctionCallSniff
{
  /**
   * Gets called by parent class on every function call
   *
   * @param File $file               the function call to process
   * @param int  $last_callee_token  (unused)
   * @param int  $parenthesis_opener the opening parenthesis of the functon call
   * @return NULL to indicate remaining function calls should be processed as usual
   */
  protected function _processCall(File $file, int $last_callee_token, int $parenthesis_opener)
  {
    $tokens=$file->getTokens();

    if(!isset($tokens[$parenthesis_opener]['parenthesis_closer']))
    {
      $warning='Could not parse function call, closing parenthesis seems to be missing';
      $file->addWarning($warning,$parenthesis_opener,'Unhandled');
      return;
    }

    $argument_ranges=$this->_fetchArgumentRanges($file,$parenthesis_opener);
    foreach($argument_ranges as $range)
      $this->_checkArgumentRange($file,$range);
  }


  private function _fetchArgumentRanges($file, int $parenthesis_opener): array
  {
    $tokens=$file->getTokens();
    $rv=[];
    $parenthesis_closer=$tokens[$parenthesis_opener]['parenthesis_closer'];
    $start=$parenthesis_opener;
    $previous_separator=$start;
    while(true)
    {
      $current=$file->findNext([T_COMMA,T_OPEN_PARENTHESIS,T_OPEN_SHORT_ARRAY,T_OPEN_CURLY_BRACKET],$start+1,NULL,false,NULL,true);
      if($current===false || $current>$parenthesis_closer)
        break;
      switch($tokens[$current]['code'])
      {
        case T_COMMA:
          $rv[]=[$previous_separator+1,$current-1];
          $start=$current;
          $previous_separator=$start;
          break;
        case T_OPEN_PARENTHESIS:
          if(!empty($tokens[$current]['parenthesis_closer']))
          {
            $start=$tokens[$current]['parenthesis_closer'];
            break;
          }
        case T_OPEN_SHORT_ARRAY:
        case T_OPEN_CURLY_BRACKET:
          $start=$tokens[$current]['bracket_closer'];
          break;
        default:
          throw new \LogicException('unhandled type');
      }
    }
    $rv[]=[$previous_separator+1,$parenthesis_closer-1];

    return $rv;
  }

  private function _checkArgumentRange(File $file, array $range): void
  {
    $tokens=$file->getTokens();

    if($range[1]<$range[0]) //empty argument list, e.g. "f()"
      return;

    $error=NULL;
    $type=NULL;
    $left_space=$tokens[$range[0]]['code']===T_WHITESPACE;
    if($range[1]==$range[0]) //only one token between parentheses, e.g. "f(1)" or "f(    )"
    {
      if($left_space)
      {
        $error='Empty function call argument list shouldn\'t contain any whitespaces';
        $type='SpaceInstead';
      }
    }
    else
    {
      if($tokens[$range[0]]['code']===T_WHITESPACE && $tokens[$range[0]]['length']==0 && $tokens[$range[0]+1]['line']>$tokens[$range[0]]['line'])
      {
        for($ti=$range[0]+1;$ti<=$range[1];$ti++)
        {
          if($tokens[$ti]['code']!==T_WHITESPACE || $ti<$range[1]&&$tokens[$ti+1]['line']>$tokens[$range[0]+1]['line'])
          {
            $left_space=$tokens[$ti]['code']===T_WHITESPACE;
            break;
          }
        }
      }
      $right_space=$tokens[$range[1]]['code']===T_WHITESPACE;
      if($left_space)
      {
        if($right_space)
        {
          $error='Function call argument shouldn\'t be wrapped in whitespaces';
          $type='SpaceAround';
        }
        else
        {
          $error='Function call argument shouldn\'t have whitespaces on left side';
          $type='SpaceBefore';
        }
      }
      elseif($right_space)
      {
        $error='Function call argument shouldn\'t have whitespaces on right side';
        $type='SpaceAfter';
      }
    }

    $this->_registerError($file,$range,$error,$type);
  }

  private function _registerError(File $file, array $range, ?string $error, ?string $type): void
  {
    if(!$error)
      return;

    $tokens=$file->getTokens();
    $target=$range[0];
    for($ti=$range[0];$ti<=$range[1];$ti++)
    {
      if($tokens[$ti]['code']!==T_WHITESPACE)
      {
        $target=$ti;
        break;
      }
    }
    $file->addError($error,$target,$type);
  }
}
