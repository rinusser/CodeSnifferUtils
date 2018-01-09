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

use PHP_CodeSniffer\Files\File;
use RN\CodeSnifferUtils\Files\FileUtils;
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

    $aligned_vertically=false;
    $call_start=$file->findStartOfStatement($last_callee_token);
    if($tokens[$call_start]['line']==$tokens[$tokens[$parenthesis_opener]['parenthesis_closer']]['line'])
    {
      $aligned_call_start_above=$this->_findAlignedFunctionCallAtLine($file,$last_callee_token,$tokens[$last_callee_token]['line']-1);
      $aligned_call_start_below=$this->_findAlignedFunctionCallAtLine($file,$last_callee_token,$tokens[$last_callee_token]['line']+1);
      if($aligned_call_start_above[0]!==false && $this->_areCommonArgumentsAligned($file,$parenthesis_opener,$aligned_call_start_above[1]))
        $aligned_vertically=true;
      elseif($aligned_call_start_below[0]!==false && $this->_areCommonArgumentsAligned($file,$parenthesis_opener,$aligned_call_start_below[1]))
        $aligned_vertically=true;
    }

    $argument_ranges=$this->_fetchArgumentRanges($file,$parenthesis_opener);
    foreach($argument_ranges as $range)
      $this->_checkArgumentRange($file,$range,$aligned_vertically);
  }

  private function _areCommonArgumentsAligned(File $file, int $f1, int $f2): bool
  {
    $tokens=$file->getTokens();
    $args1=$this->_getEffectiveStarts($file,$this->_fetchArgumentRanges($file,$f1));
    $args2=$this->_getEffectiveStarts($file,$this->_fetchArgumentRanges($file,$f2));
    $common_count=min(count($args1),count($args2));
    if($common_count<2)
      return false;

    for($ti=0;$ti<$common_count;$ti++)
    {
      $token1=$tokens[$args1[$ti]];
      $token2=$tokens[$args2[$ti]];
      if($this->_numbersAlign($file,$args1[$ti],$args2[$ti]))
        continue;
      if($token1['column']!=$token2['column'])
        return false;
    }
    return true;
  }

  private function _numbersAlign(File $file, int $number1, int $number2): bool
  {
    $tokens=$file->getTokens();
    $number1=$this->_getNumericString($file,$number1);
    $number2=$this->_getNumericString($file,$number2);
    if($number1===NULL || $number2===NULL)
      return false;

    $token1=$tokens[$args1[$ti]];
    $token2=$tokens[$args2[$ti]];

    if($token1['column']+$token1['length'] == $token2['column']+$token2['length'])
      return true;

    $dot1=strpos($number1,'.');
    $dot2=strpos($number2,'.');
    if($dot1===false||$dot2===false)
      return false;
    if($tokens1['column']+$dot1 == $tokens2['column']+$dot2)
      return true;

    return false;
  }

  private function _getNumericString(File $file, int $offset): ?string
  {
    $sign='';
    $tokens=$file->getTokens();
    if($tokens[$offset]['code']===T_MINUS)
    {
      $sign='-';
      $offset++;
    }
    if(!in_array($tokens[$offset]['code'],[T_LNUMBER,T_DNUMBER],true))
      return NULL;
    return $sign.$tokens[$offset]['content'];
  }

  private function _getEffectiveStarts(File $file, array $ranges): array
  {
    $rv=[];
    $tokens=$file->getTokens();
    foreach($ranges as $range)
    {
      for($ti=$range[0];$ti<=$range[1];$ti++)
      {
        if($tokens[$ti]['code']!==T_WHITESPACE)
        {
          $rv[]=$ti;
          continue 2;
        }
      }
      $rv[]=$range[0];
    }
    return $rv;
  }

  private function _findAlignedFunctionCallAtLine(File $file, int $start, int $line): array
  {
    $rv_no=[false,NULL];
    if($line<0)
      return $rv_no;
    $tokens=$file->getTokens();
    $offset=FileUtils::findTokenAtLineAndColumn($file,$line,$tokens[$start]['column']);
    if($offset===NULL)
      return $rv_no;
    $open_parenthesis=$file->findNext(T_OPEN_PARENTHESIS,$offset,NULL,false,NULL,true);
    if($open_parenthesis===false)
      return $rv_no;
    $callee_token=FileUtils::findLastCalleeToken($file,$open_parenthesis);
    if($callee_token!==false && $callee_token==$offset)
      return [$offset,$open_parenthesis];
    return $rv_no;
  }

  private function _fetchArgumentRanges($file, int $parenthesis_opener): array
  {
    $rv=[];
    $tokens=$file->getTokens();
    $commas=FileUtils::getSeparatingCommas($file,$parenthesis_opener);
    $separators=array_merge([$parenthesis_opener],$commas,[$tokens[$parenthesis_opener]['parenthesis_closer']]);

    $count=count($separators)-1;
    for($ti=0;$ti<$count;$ti++)
      $rv[]=[$separators[$ti]+1,$separators[$ti+1]-1];

    return $rv;
  }

  private function _checkArgumentRange(File $file, array $range, bool $aligned_vertically): void
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
      if($left_space && !$aligned_vertically)
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

    $this->_handleError($file,$range,$error,$type,$aligned_vertically);
  }

  private function _handleError(File $file, array $range, ?string $error, ?string $type, bool $aligned_vertically): void
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

    if($aligned_vertically)
    {
      $file->addError($error,$target,$type);
      return;
    }
    else
    {
      if($file->addFixableError($error,$target,$type))
        $this->_trimSpaces($file,$range,$type);
    }
  }

  private function _trimSpaces(File $file, array $range, string $type): void
  {
    $tokens=$file->getTokens();
    $file->fixer->beginChangeset();

    if($type==='SpaceBefore'||$type==='SpaceAround'||$type==='SpaceInstead')
    {
      //remove any whitespaces before the call parameter
      for($ti=$range[0];$ti<=$range[1];$ti++)
      {
        if($tokens[$ti]['code']!==T_WHITESPACE)
          break;
        $file->fixer->replaceToken($ti,'');
      }
    }

    if($type==='SpaceAfter'||$type==='SpaceAround')
    {
      //remove any whitespaces after the call parameter
      for($ti=$range[1];$ti>=$range[0];$ti--)
      {
        if($tokens[$ti]['code']!==T_WHITESPACE)
          break;
        $file->fixer->replaceToken($ti,'');
      }
    }

    $file->fixer->endChangeset();
  }
}
