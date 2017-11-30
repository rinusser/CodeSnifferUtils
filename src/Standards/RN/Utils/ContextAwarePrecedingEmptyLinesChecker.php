<?php
declare(strict_types=1);
/**
 * requires PHP version 7.1+
 * @author Richard Nusser
 * @copyright 2017 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 */

namespace RN\CodeSnifferUtils\Utils;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Util\Tokens;

/**
 * Sniff for checking preceding newlines, can check surroundings
 */
class ContextAwarePrecedingEmptyLinesChecker extends PrecedingEmptyLinesChecker
{
  protected $_expectedToken;
  protected $_ignoredTokens;

  /**
   * @param mixed $expected_token the type of token to look for when checking if multiple similar tokens are adjacent
   * @param array $ignored_tokens the list of tokens to be skipped in addition to whitespaces and visibility modifiers
   */
  public function __construct($expected_token, array $ignored_tokens=[])
  {
    $this->_expectedToken=$expected_token;
    $this->_ignoredTokens=$ignored_tokens;
  }

  /**
   * @param File  $file            the phpcs file handle to check
   * @param int   $stack_ptr       the phpcs context
   * @param array $allowed_by_type a map of allowed previous token=>distance pairs
   * @return NULL to indicate phpcs should continue processing rest of file normally
   */
  public function process(File $file, int $stack_ptr, array $allowed_by_type)
  {
    $tokens=$file->getTokens();
    $effective_start=$this->_fetchEffectiveTokenStart($file,$stack_ptr);

    $prev=$file->findPrevious(T_WHITESPACE,$effective_start-1,NULL,true);
    if($tokens[$prev]['line']>=$tokens[$stack_ptr]['line']-1)
      $prev=$this->_skipPreviousTokens($file,$prev);
    $preceding_line=$tokens[$prev]['line'];

    if(!array_key_exists($tokens[$prev]['code'],$allowed_by_type))
    {
      $error='Unhandled preceding token type "'.$tokens[$prev]['type'].'"';
      $file->addWarning($error,$prev,'UnhandledContext');
      return NULL;
    }

    $lines_between=$tokens[$effective_start]['line']-$preceding_line-1;
    $expectation=$this->_fetchExpectation($allowed_by_type,$file,$prev,$stack_ptr,false,$effective_start);

    $error_expectation=NULL;
    if(is_array($expectation))
    {
      if($lines_between<$expectation[0] || $lines_between>$expectation[1])
        $error_expectation='between '.$expectation[0].' and '.$expectation[1].' lines';
    }
    elseif($lines_between!=$expectation)
      $error_expectation='exactly '.$expectation.' line'.($expectation!=1?'s':'');

    if($error_expectation)
    {
      $prevs_name=TokenNames::getPrintableName($tokens[$prev]['code'],$tokens[$prev]['type']);
      $currents_name=TokenNames::getPrintableName($tokens[$stack_ptr]['code'],$tokens[$stack_ptr]['type']);
      $error='Expected '.$error_expectation.' between '.$prevs_name.' and '.$currents_name.', got '.$lines_between.' instead';
      $file->addError($error,$stack_ptr,'PrecedingNewlines');
    }

    return NULL;
  }


  private function _skipPreviousConsecutiveComments(File $file, int $start): int
  {
    $tokens=$file->getTokens();

    while($start>0 && $start!==false)
    {
      $start_line=$tokens[$start]['line'];
      $prev=$file->findPrevious(T_WHITESPACE,$start-1,NULL,true);
      $prev_line=$tokens[$prev]['line'];

      if($prev_line<$start_line-1)
        return $start;

      if($tokens[$prev]['code']!==T_COMMENT)
        return $start;

      $start=$prev;
    }

    throw new \LogicException("this line was supposed to be unreachable by design");
  }

  /**
   * @param File $file   the phpcsfile to look through
   * @param int  $offset the token offset to start from
   * @return int the token line to measure against
   */
  protected function _fetchEffectiveTokenStart(File $file, int $offset): int
  {
    $tokens=$file->getTokens();
    $actual_line=$tokens[$offset]['line'];

    $skipped_tokens=array_merge(Tokens::$scopeModifiers,[T_WHITESPACE],$this->_ignoredTokens);
    $preceding_offset=$file->findPrevious($skipped_tokens,$offset-1,NULL,true,NULL,true);

    $preceding_line=$tokens[$preceding_offset]['line'];
    if($preceding_line<=$actual_line && $preceding_line>$actual_line-2)
    {
      if($tokens[$preceding_offset]['code']===T_DOC_COMMENT_CLOSE_TAG)
        $offset=$tokens[$preceding_offset]['comment_opener'];
      elseif($tokens[$preceding_offset]['code']===T_COMMENT)
        $offset=$this->_skipPreviousConsecutiveComments($file,$preceding_offset);
    }
    return $offset;
  }


  /**
   * Callback function for PrecedingEmptyLinesChecker: skips properties' modifiers and whitespaces
   *
   * @param File $file  the phpcs file to look through
   * @param int  $start the token offset to start looking at
   * @return int the new offset
   */
  protected function _skipPreviousTokens(File $file, int $start): int
  {
    $tokens=$file->getTokens();
    if(in_array($tokens[$start]['code'],[T_OPEN_CURLY_BRACKET,T_CLOSE_CURLY_BRACKET,T_SEMICOLON]))
      return $start;

    $skipped_tokens=array_merge(Tokens::$scopeModifiers,[T_WHITESPACE],$this->_ignoredTokens);
    $prev=$file->findPrevious($skipped_tokens,$start-1,NULL,true,NULL,true);
    return is_bool($prev)?$start:$prev;
  }

  /**
   * @param array    $allowed_by_type   the initial expectation map
   * @param File     $file              the phpcs file handle
   * @param int      $previous          the previous token's offset
   * @param int      $current           the current token's offset
   * @param bool     $match_any         (unused)
   * @param int|NULL $effective_current start of current token's context, e.g. start of accompaning docblock
   * @return array a pair of integers
   */
  protected function _fetchExpectation(array $allowed_by_type, File $file, int $previous, int $current, bool $match_any, ?int $effective_current=NULL)
  {
    if($effective_current===NULL)
      $effective_current=$current;
    $tokens=$file->getTokens();

    if($this->_expectedToken===T_CLASS) //XXX should be extracted
    {
      //if there are file and class docblocks require 1..2 empty lines
      if($tokens[$effective_current]['code']===T_DOC_COMMENT_OPEN_TAG && $tokens[$previous]['code']===T_DOC_COMMENT_CLOSE_TAG)
        return [1,2];
    }

    if($tokens[$previous]['code']!==T_SEMICOLON)
      return $allowed_by_type[$tokens[$previous]['code']];

    $previous_entry=$file->findPrevious($this->_expectedToken,$previous-1,NULL,false,NULL,true);
    if($previous_entry===false)
      return [1,2];

    if($this->_expectedToken===T_VARIABLE) //XXX this code should probably be passed by method reference in constructor
    {
      $previous_properties=$file->getMemberProperties($previous_entry);
      $current_properties=$file->getMemberProperties($current);
      return $previous_properties['is_static']==$current_properties['is_static']?[0,1]:[1,2];
    }
    elseif($this->_expectedToken===T_FUNCTION)
    {
      $previous_properties=$file->getMethodProperties($previous_entry);
      $current_properties=$file->getMethodProperties($current);
      if($previous_properties['is_abstract']==$current_properties['is_abstract'])
        return $previous_properties['is_static']==$current_properties['is_static']?[0,2]:[1,2];
      else
        return [1,2];
    }

    return [0,1];
  }
}
