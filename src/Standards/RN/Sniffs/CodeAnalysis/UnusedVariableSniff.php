<?php
declare(strict_types=1);
/**
 * requires PHP version 7.1+
 * @author Richard Nusser
 * @copyright 2017-2018 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 * @link https://github.com/rinusser/CodeSnifferUtils
 */

namespace RN\CodeSnifferUtils\Standards\RN\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;
use RN\CodeSnifferUtils\Config\PerFileSniffConfig;
use RN\CodeSnifferUtils\Utils\NoImplicitProperties;
use RN\CodeSnifferUtils\Files\FileUtils;

/**
 * This sniff checks for unused varibles.
 * It will ignore function parameters, they're covered by IgnorableUnusedFunctionParameterSniff.
 */
class UnusedVariableSniff implements Sniff
{
  use PerFileSniffConfig;

  //disallow access to undeclared properties
  use NoImplicitProperties;


  /**
   * Called by phpcs, this returns the list of tokens to listen for
   *
   * @return array
   */
  public function register()
  {
    return [T_FUNCTION,T_CLOSURE];
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
    $start=$tokens[$stack_ptr]['scope_opener'];
    $end=$tokens[$stack_ptr]['scope_closer'];
    $whitelist=[];
    foreach($file->getMethodParameters($stack_ptr) as $parameter)
      $whitelist[]=$parameter['name'];

    $variables=[];
    if($tokens[$stack_ptr]['code']===T_CLOSURE)
      foreach(FileUtils::getClosureImports($file,$stack_ptr) as $name)
        $this->_handleWriteAccess($variables,$name,$stack_ptr);

    $current=$start;
    while($current<$end)
    {
      $current=$file->findNext([T_VARIABLE,T_CLOSURE,T_DOUBLE_QUOTED_STRING],$current+1,$end);
      if($current===false)
        break;

      $special=$this->_processSpecialCases($file,$current,$variables,$whitelist);
      if($special)
      {
        $current=$special;
        continue;
      }

      $name=$tokens[$current]['content'];
      if($this->_isWriteAccess($file,$current))
        $this->_handleWriteAccess($variables,$name,$current);
      else
        $this->_handleReadAccess($variables,$name,$current);
    }

    $this->_registerWarnings($file,$variables);
  }

  private function _registerWarnings(File $file, array $variables): void
  {
    foreach($variables as $name=>$variable)
      if(!$variable['read'])
        $file->addWarning('Found unused variable %s',$variable['token'],'Unused',[$name]);
  }

  private function _processSpecialCases(File $file, int $current, array &$variables, array $whitelist): ?int
  {
    $tokens=$file->getTokens();
    if($tokens[$current]['code']===T_CLOSURE)
    {
      foreach(FileUtils::getClosureImports($file,$current) as $name)
        $this->_handleReadAccess($variables,$name,$current);
      return $tokens[$current]['scope_closer'];
    }
    elseif($tokens[$current]['code']===T_DOUBLE_QUOTED_STRING)
    {
      preg_match_all('/\$[a-z_][a-z0-9_]+/i',$tokens[$current]['content'],$matches);
      foreach($matches[0] as $name)
        $this->_handleReadAccess($variables,$name,$current);
      return $current;
    }

    $name=$tokens[$current]['content'];
    if(in_array($name,$whitelist))
      return $current;

    return NULL;
  }

  private function _handleWriteAccess(array &$variables, string $name, int $offset)
  {
    if(!isset($variables[$name]))
      $variables[$name]=['token'=>$offset,'read'=>false];
  }

  private function _handleReadAccess(array &$variables, string $name)
  {
    if(isset($variables[$name]))
      $variables[$name]['read']=true;
  }

  private function _isWriteAccess(File $file, int $current): bool
  {
    $tokens=$file->getTokens();

    $simple_result=$this->_doSimpleWriteChecks($file,$current);
    if(is_bool($simple_result))
      return $simple_result;
    $prev=$simple_result;

    $array_start=NULL;
    $code=$tokens[$prev]['code'];
    switch($code)
    {
      case T_GLOBAL:
        return true;
      case T_OPEN_SHORT_ARRAY:
        $array_start=$prev;
        break;
      case T_OPEN_PARENTHESIS:
        $array_start=$prev;
        $prev=$file->findPrevious(Tokens::$emptyTokens,$prev-1,NULL,true);
        if($prev===false || !in_array($tokens[$prev]['code'],[T_ARRAY,T_LIST],true))
          return false;
        break;
      case T_AS:
        return true;
      default:
        return false;
    }

    //if array is followed by assignment operator it's a destructuring write
    $next=$file->findNext(Tokens::$emptyTokens,($tokens[$array_start]['bracket_closer']??$tokens[$array_start]['parenthesis_closer'])+1,NULL,true);
    if($next!==false && in_array($tokens[$next]['code'],Tokens::$assignmentTokens,true))
      return true;

    //if there's an "as" keyword before the array it's a foreach iteration variable, that's a write access
    $skip=array_merge(Tokens::$emptyTokens,[T_VARIABLE,T_COMMA,T_DOUBLE_ARROW]);
    $prev=$file->findPrevious($skip,$array_start-1,NULL,true);
    if($prev!==false && $tokens[$prev]['code']===T_AS)
      return true;

    return false;
  }

  private function _doSimpleWriteChecks(File $file, int $current)
  {
    $tokens=$file->getTokens();
    $prev=$file->findPrevious(Tokens::$emptyTokens,$current-1,NULL,true);

    //skip class/object member accesses, e.g. SomeClass::$a or $this->$b
    if($prev!==false && in_array($tokens[$prev]['code'],[T_DOUBLE_COLON,T_OBJECT_OPERATOR],true))
      return false;

    $next=$file->findNext(Tokens::$emptyTokens,$current+1,NULL,true);
    if($next!==false)
    {
      //any variable immediately followed by a double arrow is either a foreach() iterator or an array key
      if($tokens[$next]['code']===T_DOUBLE_ARROW)
        return $tokens[$prev]['code']===T_AS;

      //any variable immediately followed by an assignment operator must be a write access
      if(in_array($tokens[$next]['code'],Tokens::$assignmentTokens,true))
        return true;
    }

    if(FileUtils::checkForTokenSequence($file,$next,[T_OPEN_SQUARE_BRACKET,T_CLOSE_SQUARE_BRACKET,T_EQUAL],Tokens::$emptyTokens))
      return true;

    $skip=array_merge(Tokens::$emptyTokens,[T_VARIABLE,T_COMMA,T_DOUBLE_ARROW]);
    $prev=$file->findPrevious($skip,$current-1,NULL,true);
    if($prev===false)
      return false;
    if($tokens[$prev]['code']===T_AS)
      return true;

    return $prev;
  }
}
