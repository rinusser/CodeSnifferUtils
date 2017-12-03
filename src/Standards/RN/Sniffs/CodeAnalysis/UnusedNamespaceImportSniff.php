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
use RN\CodeSnifferUtils\Utils\PerFileSniffConfig;

/**
 * This checks for unused namespace imports
 */
class UnusedNamespaceImportSniff extends AbstractScopeSniff
{
  use PerFileSniffConfig;

  /**
   * Called by phpcs, this returns the list of tokens to listen for
   * @return array
   */
  public function __construct()
  {
    parent::__construct([T_FUNCTION,T_CLASS],[T_USE],true);
  }


  protected function processTokenOutsideScope(File $file, $stack_ptr)  //CSU.IgnoreName: required by parent class
  {
    if($this->_isDisabledInFile($file))
      return;

    $tokens=$file->getTokens();

    $end=$file->findNext(T_SEMICOLON,$stack_ptr+1,NULL,false);
    if(!$end)
    {
      $warning='Could not parse "use" statement: expected semicolon somewhere after it';
      $file->addWarning($warning,$stack_ptr,'NoSemicolonFound');
      return;
    }

    $str=trim($file->getTokensAsString($stack_ptr+1,$end-$stack_ptr-1));

    //skip lambdas
    if(strpos($str,'(')!==false)
      return;

    if(!preg_match('/^([^\\\\]*\\\\)*{?([^\\\\]+ +as +)?([^\\\\}]+)}?$/',$str,$matches))
    {
      $warning='Could not parse "use" statement: unexpected format';
      $file->addWarning($warning,$stack_ptr,'UnexpectedFormat');
      return;
    }

    $imported=array_map('trim',explode(',',$matches[3]));

    foreach($imported as $import)
      $this->_checkUsage($file,$stack_ptr,$import);
  }

  protected function _checkUsage(File $file, int $import_offset, string $name)
  {
    $tokens=$file->getTokens();
    $start=$import_offset;
    while(true)
    {
      $candidate=$file->findNext(T_STRING,$file->findEndOfStatement($start),NULL,false,$name);
      if($candidate===false)
        return $this->_warnUnused($file,$import_offset,$name);

      $statement_start=$file->findStartOfStatement($candidate,[T_COMMA]);
      $is_namespace_import=$tokens[$statement_start]['code']===T_USE && !$file->hasCondition($statement_start,[T_CLASS,T_INTERFACE,T_TRAIT]);
      if($is_namespace_import || $tokens[$candidate-1]['code']===T_NS_SEPARATOR)
      {
        $start=$candidate;
        continue;
      }

      break;
    }
  }

  protected function _warnUnused(File $file, int $import_offset, string $name)
  {
    $warning='Namespace import "'.$name.'" seems unused in file';
    $file->addWarning($warning,$import_offset,'Unused');
  }


  protected function processTokenWithinScope(File $file, $stack_ptr, $curr_scope)  //CSU.IgnoreName: required by parent class
  {
  }
}
