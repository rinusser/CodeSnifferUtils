<?php
declare(strict_types=1);
/**
 * requires PHP version 7.1+
 * @author Richard Nusser
 * @copyright 2017 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 * @link https://github.com/rinusser/CodeSnifferUtils
 */

namespace RN\CodeSnifferUtils\Checkers;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Util\Tokens;
use RN\CodeSnifferUtils\Files\FileUtils;
use RN\CodeSnifferUtils\Utils\NoImplicitProperties;
use RN\CodeSnifferUtils\Utils\TokenNames;

/**
 * Base sniff for checking preceding newlines.
 */
class PrecedingEmptyLinesChecker
{
  public const T_ANY='_any';


  //disallow access to undeclared properties
  use NoImplicitProperties;


  protected $_allowedByType;

  protected $_matchAny;

  /**
   * @param array $allowed_by_type a map of allowed previous token=>distance pairs
   */
  public function __construct(array $allowed_by_type)
  {
    $this->_allowedByType=$allowed_by_type;
    $this->_matchAny=count($allowed_by_type==1) && isset($allowed_by_type[self::T_ANY]);
  }


  /**
   * Process a file's token - should be called by other sniffs' process() methods
   *
   * @param File $file      the phpcs file handle
   * @param int  $stack_ptr the token offset to be processed
   * @return NULL to indicate phpcs should continue processing rest of file normally
   */
  public function process(File $file, int $stack_ptr)
  {
    $tokens=$file->getTokens();
    $effective_start=$this->_fetchEffectiveTokenStart($file,$stack_ptr);

    $prev=$file->findPrevious(T_WHITESPACE,$effective_start-1,NULL,true);
    if(!$this->_matchAny)
      $prev=$this->_skipPreviousTokens($file,$prev);

    $preceding_line=$tokens[$prev]['line'];

    if(!array_key_exists($tokens[$prev]['code'],$this->_allowedByType) && !$this->_matchAny)
    {
      $error='Unhandled preceding token type "'.$tokens[$prev]['type'].'"';
      $file->addWarning($error,$prev,'UnhandledContext');
      return NULL;
    }

    $lines_between=$tokens[$effective_start]['line']-$preceding_line-1;
    $expectation=$this->_fetchExpectation($file,$prev,$stack_ptr);

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

  protected function _fetchEffectiveTokenStart(File $file, int $stack_ptr): int
  {
    return $stack_ptr;
  }


  /**
   * Fetch the expected distance to the previous token
   *
   * @param File $file     the current phpcs file
   * @param int  $previous the previous token to check distance to
   * @param int  $current  (unused) the current token to compare distance to
   * @return int|array
   */
  protected function _fetchExpectation(File $file, int $previous, int $current)
  {
    $tokens=$file->getTokens();
    $type=$this->_matchAny?self::T_ANY:$tokens[$previous]['code'];
    return $this->_allowedByType[$type];
  }

  protected function _skipPreviousTokens(File $file, int $start): int
  {
    $tokens=$file->getTokens();
    $start=$this->_skipSingleLineCodeComment($file,$start);
    if($tokens[$start]['code']===T_SEMICOLON)
    {
      $prev_start=$file->findPrevious(Tokens::$scopeOpeners,$start-1,NULL,false,NULL,true);
      if($prev_start===false)
      {
        $warning='Unhandled preceding statement type';
        $file->addWarning($warning,$start,'UnknownContext');
        return $start;
      }
      $start=$prev_start;
    }
    return $start;
  }

  /**
   * Advances the current token pointer if it currently points to a single-line comment on executed code
   *
   * @param File $file  the phpcs file handle to look in
   * @param int  $start the current token pointer
   * @return int the new token pointer
   */
  protected function _skipSingleLineCodeComment(File $file, int $start): int
  {
    $tokens=$file->getTokens();
    if($tokens[$start]['code']!==T_COMMENT)
      return $start;

    $prev=FileUtils::findPreviousOnLineExcept($file,[T_COMMENT,T_WHITESPACE],$start);
    return $prev===false?$start:$prev;
  }
}
