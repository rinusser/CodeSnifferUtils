<?php
declare(strict_types=1);
/**
 * requires PHP version 7.0+
 * @author Richard Nusser
 * @copyright 2017 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 */

namespace RN\CodeSnifferUtils\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\AbstractScopeSniff;

/**
 * This checks for unused namespace imports
 */
class UnusedNamespaceImportSniff extends AbstractScopeSniff
{
  /**
   * Called by phpcs, this returns the list of tokens to listen for
   * @return array
   */
  public function __construct()
  {
    parent::__construct([T_FUNCTION,T_CLASS],[T_USE],true);
  }


  protected function processTokenOutsideScope(File $phpcsFile, $stackPtr)
  {
    $tokens=$phpcsFile->getTokens();

    $end=$phpcsFile->findNext(T_SEMICOLON,$stackPtr+1,NULL,false);
    if(!$end)
    {
      $warning='Could not parse "use" statement: expected semicolon somewhere after it';
      $phpcsFile->addWarning($warning,$stackPtr,'NoSemicolonFound');
      return;
    }

    $str=trim($phpcsFile->getTokensAsString($stackPtr+1,$end-$stackPtr-1));

    //skip lambdas
    if(strpos($str,'(')!==false)
      return;

    if(!preg_match('/^([^\\\\]*\\\\)*{?([^\\\\]+ +as +)?([^\\\\}]+)}?$/',$str,$matches))
    {
      $warning='Could not parse "use" statement: unexpected format';
      $phpcsFile->addWarning($warning,$stackPtr,'UnexpectedFormat');
      return;
    }

    $imported=array_map('trim',explode(',',$matches[3]));

    foreach($imported as $import)
      $this->_checkUsage($phpcsFile,$stackPtr,$import);
  }

  protected function _checkUsage(File $phpcsFile, int $import_offset, string $name)
  {
    $tokens=$phpcsFile->getTokens();
    $start=$import_offset;
    while(true)
    {
      $candidate=$phpcsFile->findNext(T_STRING,$phpcsFile->findEndOfStatement($start),NULL,false,$name);
      if($candidate===false)
        return $this->_warnUnused($phpcsFile,$import_offset,$name);

      $statement_start=$phpcsFile->findStartOfStatement($candidate,[T_COMMA]);
      $is_namespace_import=$tokens[$statement_start]['code']===T_USE && !$phpcsFile->hasCondition($statement_start,[T_CLASS,T_INTERFACE,T_TRAIT]);
      if($is_namespace_import || $tokens[$candidate-1]['code']===T_NS_SEPARATOR)
      {
        $start=$candidate;
        continue;
      }

      break;
    }
  }

  protected function _warnUnused(File $phpcsFile, int $import_offset, string $name)
  {
    $warning='Namespace import "'.$name.'" seems unused in file';
    $phpcsFile->addWarning($warning,$import_offset,'Unused');
  }


  protected function processTokenWithinScope(File $phpcsFile, $stackPtr, $currScope)
  {
  }
}
