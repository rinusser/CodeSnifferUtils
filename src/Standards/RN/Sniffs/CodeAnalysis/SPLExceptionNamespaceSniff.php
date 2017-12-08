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
use RN\CodeSnifferUtils\Utils\PerFileSniffConfig;
use RN\CodeSnifferUtils\Utils\FileUtils;

/**
 * This checks for built-in SPL exception referenced in wrong namespace
 */
class SPLExceptionNamespaceSniff implements Sniff
{
  private const SPL_EXCEPTIONS=['Exception','BadFunctionCallException','BadMethodCallException','DomainException',
                                'InvalidArgumentException','LengthException','LogicException','OutOfBoundsException',
                                'OutOfRangeException','OverflowException','RangeException','RuntimeException',
                                'UnderflowException','UnexpectedValueException'];

  //import per-file config
  use PerFileSniffConfig;

  /**
   * Called by phpcs, this returns the list of tokens to listen for
   * @return array
   */
  public function register()
  {
    return [T_NEW,T_CATCH];
  }

  /**
   * @param File $file      the phpcs file handle to check
   * @param int  $stack_ptr the phpcs context
   * @return void
   */
  public function process(File $file, $stack_ptr)
  {
    if($this->_isDisabledInFile($file))
      return $file->numTokens;

    $file_namespace=FileUtils::getFileNamespace($file);

    //if the entire file is in the root namespace there's not much point in checking: any namespace references are considered to be intentional
    if($file_namespace===FileUtils::ROOT_NAMESPACE)
      return;

    $imported=FileUtils::getNamespaceImports($file);
    $tokens=$file->getTokens();

    if($tokens[$stack_ptr]['code']===T_CATCH)
    {
      $exceptions=FileUtils::getCaughtExceptions($file,$stack_ptr);
      foreach($exceptions as $exception)
      {
        if($exception[0]==='\\' || !in_array($exception,self::SPL_EXCEPTIONS))
          continue;
        $this->_checkExceptionReference($exception,$file,$stack_ptr,$imported);
      }
    }
    elseif($tokens[$stack_ptr]['code']===T_NEW)
    {
      $end=$file->findNext(T_SEMICOLON,$stack_ptr+1,NULL,false);
      $class=trim($file->getTokensAsString($stack_ptr+1,$end-$stack_ptr-1));
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

      $this->_checkExceptionReference($exception,$file,$stack_ptr,$imported);
    }
    else
      throw new \LogicException('unhandled token type');
  }

  protected function _checkExceptionReference(string $class, File $file, int $stack_ptr, array $imported_classes)
  {
    if(in_array($class,$imported_classes))
      return;
    $warning='Possible wrong namespace reference to "'.$class.'", did you mean "\\'.$class.'"?';
    $file->addWarning($warning,$stack_ptr,'WrongNamespace');
  }
}
