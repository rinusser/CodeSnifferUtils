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
use RN\CodeSnifferUtils\Config\PerFileSniffConfig;
use RN\CodeSnifferUtils\Utils\NoImplicitProperties;
use RN\CodeSnifferUtils\Files\FileUtils;

/**
 * Ensures assignment operators aren't surrounded by any whitespaces, unless aligned vertically
 */
class AssignmentOperatorsSniff implements Sniff
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
    return [T_EQUAL,T_AND_EQUAL,T_OR_EQUAL,T_CONCAT_EQUAL,T_DIV_EQUAL,T_MINUS_EQUAL,T_POW_EQUAL,T_MOD_EQUAL,
            T_MUL_EQUAL,T_PLUS_EQUAL,T_XOR_EQUAL,T_SL_EQUAL,T_SR_EQUAL,T_COALESCE_EQUAL,T_DOUBLE_ARROW];
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

    $grid_alignment=$this->_findVerticalAlignment($file,$stack_ptr);
    $space_before=$grid_alignment!==0 && ($tokens[$stack_ptr-1]['code']??NULL)===T_WHITESPACE;
    $space_after=($tokens[$stack_ptr+1]['code']??NULL)===T_WHITESPACE;

    [$code,$message]=$this->_getErrorCodeAndMessage($space_before,$space_after,$grid_alignment!==NULL);
    if($code!==NULL)
    {
      if($space_before && $grid_alignment<0)
        $file->addError($message,$stack_ptr,$code);
      else
      {
        if($file->addFixableError($message,$stack_ptr,$code))
        {
          $file->fixer->beginChangeset();
          if($space_after)
            $file->fixer->replaceToken($stack_ptr+1,'');
          if($space_before)
            $file->fixer->replaceToken($stack_ptr-1,substr($tokens[$stack_ptr-1]['content'],0,-$grid_alignment));
          $file->fixer->endChangeset();
        }
      }
    }
  }

  private function _findVerticalAlignment(File $file, int $stack_ptr): ?int
  {
    $results_above=$this->_checkDirection($file,$stack_ptr,-1);
    $results_below=$this->_checkDirection($file,$stack_ptr,1);

    //there's nothing to do if there isn't an identical operator above or below
    $aligned=$results_above[0]||$results_below[0];
    if(!$aligned)
      return NULL;

    //if the vertical operator column immediately follows a non-whitespace token somewhere above or below consider the current line aligned as well
    if($results_above[1]===0||$results_below[1]===0)
      return 0;

    //only include the whitespace lengths in the directions that actually align
    $minded=[];
    foreach([$results_above,$results_below] as $result)
      if($result[0])
        $minded[]=$result[1];

    //return the lowest whitespace length: this amount of spaces needs to be removed in the entire block of lines
    return min($minded);
  }

  private function _checkDirection(File $file, int $stack_ptr, int $delta): array
  {
    $tokens=$file->getTokens();
    $left_aligned=999999;
    $aligned=false;
    $token=$tokens[$stack_ptr];
    $column=$token['column'];
    $code=$token['code'];

    for($tl=$token['line']+$delta;$tl>=0 && $tl<=$file->numTokens;$tl+=$delta)
    {
      $offset=FileUtils::findTokenAtLineAndColumn($file,$tl,$column,[$code]);
      if($offset===NULL)
        break;
      $aligned=true;
      if(($tokens[$offset-1]['code']??NULL)!==T_WHITESPACE)
        $left_aligned=0;
      elseif(($tokens[$offset-2]['code']??NULL)!==T_WHITESPACE && $tokens[$offset-2]['line']==$tokens[$offset]['line'])
      {
        if(trim($tokens[$offset-1]['content'],' ')!=='')
          $left_aligned=-1;
        elseif($left_aligned>0)
          $left_aligned=min($left_aligned,strlen($tokens[$offset-1]['content']));
      }
    }

    if($left_aligned>999998)
      $left_aligned=-1;

    return [$aligned,$left_aligned];
  }

  private function _getErrorCodeAndMessage(bool $before, bool $after, bool $is_grid): array
  {
    $grid_suffix=$is_grid?' beyond vertical alignment':'';
    if($before&&$after)
      return ['SpaceAround','There must not be any spaces or newlines around assignment operators'.$grid_suffix];
    if($before)
      return ['SpaceBefore','There must not be any spaces or newlines before assignment operators'.$grid_suffix];
    if($after)
      return ['SpaceAfter','There must not be any spaces or newlines after assignment operators'];
    return [NULL,NULL];
  }
}
