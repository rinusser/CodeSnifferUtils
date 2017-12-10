<?php
declare(strict_types=1);
/**
 * requires PHP version 7.0+
 * @author Richard Nusser
 * @copyright 2017 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 * @link https://github.com/rinusser/CodeSnifferUtils
 */

namespace PHP_CodeSniffer\Standards\RN\Sniffs\Commenting; //XXX phpcs's property injection doesn't work for sniffs in foreign namespaces

use PHP_CodeSniffer\Standards\PEAR\Sniffs\Commenting\FunctionCommentSniff;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Util\Tokens;
use RN\CodeSnifferUtils\Utils\PerFileSniffConfig;

/**
 * This is pretty much PEAR.Commenting.FunctionComment, just with minimum visibility configuration
 */
class ConfigurableFunctionCommentSniff extends FunctionCommentSniff
{
  use PerFileSniffConfig;


  public $minimumVisibility='private';


  /**
   * @param File $file      the phpcs file handle to check
   * @param int  $stack_ptr the phpcs context
   * @return mixed see parent class
   */
  public function process(File $file, $stack_ptr)
  {
    if($this->_isDisabledInFile($file))
      return;

    $tokens=$file->getTokens();
    $prev=$file->findPrevious(array_merge(Tokens::$scopeModifiers,[T_WHITESPACE]),$stack_ptr-1,NULL,true);
    if($tokens[$prev]['code']!==T_DOC_COMMENT_CLOSE_TAG)
    {
      $properties=$file->getMethodProperties($stack_ptr);
      if($this->minimumVisibility=='protected')
      {
        if($properties['scope']=='private')
          return;
      }
      elseif($this->minimumVisibility!='private' && $properties['scope']!='public')
       return;
    }

    return parent::process($file,$stack_ptr);
  }

  protected function processReturn(File $file, $stack_ptr, $comment_start)  //CSU.IgnoreName: required by parent class
  {
    //special handling for tests: don't inspect PHPUnit setup/teardown and test methods too closely, especially skip the @return tag check
    if(preg_match('#/tests/.*Test\.php$#',$file->getFilename()))
    {
      $method_name=$file->getDeclarationName($stack_ptr);

      //PHPUnit setup/teardown methods
      if(in_array($method_name,['setUpBeforeClass','tearDownAfterClass','setUp','tearDown']))
        return;

      //test methods
      if(substr($method_name,0,4)==='test')
        return;
    }
    return parent::processReturn($file,$stack_ptr,$comment_start);
  }
}
