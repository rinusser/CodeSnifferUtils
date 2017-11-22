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
use PHP_CodeSniffer\Sniffs\Sniff;
use RN\CodeSnifferUtils\Utils\Debug;

/**
 * This checks for built-in SPL exception referenced in wrong namespace
 */
class SPLExceptionNamespaceSniff implements Sniff
{
  private const ROOT_NS='\\';
  private const SPL_EXCEPTIONS=['Exception','BadFunctionCallException','BadMethodCallException','DomainException',
                                'InvalidArgumentException','LengthException','LogicException','OutOfBoundsException',
                                'OutOfRangeException','OverflowException','RangeException','RuntimeException',
                                'UnderflowException','UnexpectedValueException'];


  /**
   * Called by phpcs, this returns the list of tokens to listen for
   * @return array
   */
  public function register()
  {
    return [T_NEW,T_CATCH];
  }

  /**
   * @param File $phpcsFile the phpcs file handle to check
   * @param int  $stackPtr  the phpcs context
   * @return void
   */
  public function process(File $phpcsFile, $stackPtr)
  {
    $file_namespace=$this->_findFileNamespace($phpcsFile);

    //if the entire file is in the root namespace there's not much point in checking: any namespace references are considered to be intentional
    if($file_namespace===self::ROOT_NS)
      return;

    $imported=$this->_findRootNSImports($phpcsFile);
    $tokens=$phpcsFile->getTokens();

    if($tokens[$stackPtr]['code']===T_CATCH)
    {
      $start=$tokens[$stackPtr]['parenthesis_opener']+1;
      $caught=$phpcsFile->getTokensAsString($start,$tokens[$stackPtr]['parenthesis_closer']-$start);
      $caught=substr($caught,0,strpos($caught,'$'));
      $exceptions=array_map('trim',explode('|',$caught));
      foreach($exceptions as $exception)
      {
        if($exception[0]==='\\' || !in_array($exception,self::SPL_EXCEPTIONS))
          continue;
        $this->_checkExceptionReference($exception,$phpcsFile,$stackPtr,$imported);
      }
    }
    elseif($tokens[$stackPtr]['code']===T_NEW)
    {
      $end=$phpcsFile->findNext(T_SEMICOLON,$stackPtr+1,NULL,false);
      $class=trim($phpcsFile->getTokensAsString($stackPtr+1,$end-$stackPtr-1));
      if($class[0]==='\\')
        return;

      preg_match('/^([^(]+)/',$class,$matches);
      $exception=trim($matches[1]);

      //if there's a $ in the exception name it's a variable class instantiation
      //if there's a \ it's a namespace reference
      if(strpos($exception,'$')!==false || strpos($exception,'\\')!==false)
        return;

      if(!in_array($exception,self::SPL_EXCEPTIONS))
        return;

      $this->_checkExceptionReference($exception,$phpcsFile,$stackPtr,$imported);
    }
    else
      throw new \LogicException('unhandled token type');
  }

  protected function _checkExceptionReference(string $class, File $phpcsFile, int $stackPtr, array $imported_classes)
  {
    if(in_array($class,$imported_classes))
      return;
    $warning='Possible wrong namespace reference to "'.$class.'", did you mean "\\'.$class.'"?';
    $phpcsFile->addWarning($warning,$stackPtr,'WrongNamespace');
  }

  protected function _findFileNamespace(File $phpcsFile): string
  {
    $ns=$phpcsFile->findNext(T_NAMESPACE,0,NULL,false);
    if($ns===false)
      return self::ROOT_NS;

    $ns_end=$phpcsFile->findNext(T_SEMICOLON,$ns,NULL,false);
    $namespace=trim($phpcsFile->getTokensAsString($ns+1,$ns_end-$ns-1));
    return $namespace;
  }

  protected function _findRootNSImports(File $phpcsFile): array
  {
    $rv=[];
    $tokens=$phpcsFile->getTokens();
    $current=0;
    while(true)
    {
      $current=$phpcsFile->findNext(T_USE,$current+1,NULL,false);
      if($current===false)
        break;
      if($tokens[$current]['column']!=1)
        continue;
      $end=$phpcsFile->findNext(T_SEMICOLON,$current+1,NULL,false);
      $use=trim($phpcsFile->getTokensAsString($current+1,$end-$current-1));
      if(preg_match('/^\\\\?([^\\\\]+)$/',$use,$matches))
        $rv[]=$matches[1];
    }
    return $rv;
  }
}
