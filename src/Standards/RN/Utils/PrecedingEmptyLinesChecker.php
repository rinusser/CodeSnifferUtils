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
 * Base sniff for checking preceding newlines.
 */
class PrecedingEmptyLinesChecker
{
  const T_ANY='_any';


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
    $match_any=count($allowed_by_type==1) && isset($allowed_by_type[self::T_ANY]);

    $prev=$file->findPrevious(T_WHITESPACE,$effective_start-1,NULL,true);
    if(!$match_any)
      $prev=$this->_skipPreviousTokens($file,$prev);

    $preceding_line=$tokens[$prev]['line'];

    if(!array_key_exists($tokens[$prev]['code'],$allowed_by_type) && !$match_any)
    {
      $error='Unhandled preceding token type "'.$tokens[$prev]['type'].'"';
      $file->addWarning($error,$prev,'UnhandledContext');
      return NULL;
    }

    $lines_between=$tokens[$effective_start]['line']-$preceding_line-1;
    $expectation=$this->_fetchExpectation($allowed_by_type,$file,$prev,$stack_ptr,$match_any);

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
   * @param array $allowed_by_type a list of token type=>distance values
   * @param File  $file            the current phpcs file
   * @param int   $previous        the previous token to check distance to
   * @param int   $current         (unused) the current token to compare distance to
   * @param bool  $match_any       whether to check distance to any previous tag (true) or a specific one (false)
   * @return int|array
   */
  protected function _fetchExpectation(array $allowed_by_type, File $file, int $previous, int $current, bool $match_any)
  {
    $tokens=$file->getTokens();
    $type=$match_any?self::T_ANY:$tokens[$previous]['code'];
    return $allowed_by_type[$type];
  }

  protected function _skipPreviousTokens(File $file, int $start): int
  {
    $tokens=$file->getTokens();
    if($tokens[$start]['code']==T_SEMICOLON)
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
}
