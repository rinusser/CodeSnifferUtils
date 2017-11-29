<?php
declare(strict_types=1);
/**
 * requires PHP version 7.0+
 * @author Richard Nusser
 * @copyright 2017 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 */

namespace PHP_CodeSniffer\Standards\RN\Sniffs\Commenting; //XXX phpcs's property injection doesn't work for sniffs in foreign namespaces

use PHP_CodeSniffer\Standards\PEAR\Sniffs\Commenting\FunctionCommentSniff;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Util\Tokens;

/**
 * This is pretty much PEAR.Commenting.FunctionComment, just with minimum visibility configuration
 */
class ConfigurableFunctionCommentSniff extends FunctionCommentSniff
{
  public $minimumVisibility='private';


  /**
   * @param File $phpcsFile the phpcs file handle to check
   * @param int  $stackPtr  the phpcs context
   * @return mixed see parent class
   */
  public function process(File $phpcsFile, $stackPtr)
  {
    $tokens=$phpcsFile->getTokens();
    $prev=$phpcsFile->findPrevious(array_merge(Tokens::$scopeModifiers,[T_WHITESPACE]),$stackPtr-1,NULL,true);
    if($tokens[$prev]['code']!==T_DOC_COMMENT_CLOSE_TAG)
    {
      $properties=$phpcsFile->getMethodProperties($stackPtr);
      if($this->minimumVisibility=='protected')
      {
        if($properties['scope']=='private')
          return;
      }
      elseif($this->minimumVisibility!='private' && $properties['scope']!='public')
       return;
    }

    return parent::process($phpcsFile,$stackPtr);
  }

  protected function processReturn(File $phpcsFile, $stackPtr, $commentStart)  //CSU.IgnoreName: required by parent class
  {
    //special handling for tests: don't inspect PHPUnit setup/teardown and test methods too closely, especially skip the @return tag check
    if(preg_match('#/tests/.*Test\.php$#',$phpcsFile->getFilename()))
    {
      $method_name=$phpcsFile->getDeclarationName($stackPtr);

      //PHPUnit setup/teardown methods
      if(in_array($method_name,['setUpBeforeClass','tearDownAfterClass','setUp','tearDown']))
        return;

      //test methods
      if(substr($method_name,0,4)==='test')
        return;
    }
    return parent::processReturn($phpcsFile,$stackPtr,$commentStart);
  }
}
