<?php
declare(strict_types=1);
/**
 * requires PHP version 7.0+
 * @author Richard Nusser
 * @copyright 2017 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 */

namespace PHP_CodeSniffer\Standards\RN\Sniffs\Commenting; //XXX phpcs's property injection doesn't work for sniffs in foreign namespaces

use PHP_CodeSniffer\Standards\PEAR\Sniffs\Commenting\FileCommentSniff;
use PHP_CodeSniffer\Files\File;
use RN\CodeSnifferUtils\Sniffs\Commenting\RequireAuthorEmail;
use RN\CodeSnifferUtils\Sniffs\Commenting\CheckTagContent;

/**
 * This is similar to PEAR.Commenting.FileComment, but with configurable features
 */
class ConfigurableFileCommentSniff extends FileCommentSniff
{
  //include requireAuthorEmail property and handling
  use RequireAuthorEmail;

  //include expected content properties and content checker
  use CheckTagContent;

  public $requiredTags=NULL;

  /**
   * @param File $phpcsFile the phpcs file handle to check
   * @param int  $stackPtr  the phpcs context
   * @return mixed see parent class
   */
  public function process(File $phpcsFile, $stackPtr)
  {
    require_once('CommentSniffConfigurator.php');
    \RN\CodeSnifferUtils\Sniffs\Commenting\CommentSniffConfigurator::parseRequiredTags($this->requiredTags,$this->tags);
    return parent::process($phpcsFile,$stackPtr);
  }


  protected function processCategory($phpcsFile, array $tags)
  {
    $this->_checkTagContent($phpcsFile,'category',$tags);
    parent::processCategory($phpcsFile,$tags);
  }

  protected function processPackage($phpcsFile, array $tags)
  {
    $this->_checkTagContent($phpcsFile,'package',$tags);
    parent::processPackage($phpcsFile,$tags);
  }

  protected function processSubpackage($phpcsFile, array $tags)
  {
    $this->_checkTagContent($phpcsFile,'subpackage',$tags);
    parent::processSubpackage($phpcsFile,$tags);
  }

  protected function processAuthor($phpcsFile, array $tags)
  {
    $this->_checkTagContent($phpcsFile,'author',$tags);
    $unhandled=$this->_processAuthorWrapper($phpcsFile,$tags);
    if($unhandled)
      parent::processAuthor($phpcsFile,$unhandled);
  }

  protected function processCopyright($phpcsFile, array $tags)
  {
    $this->_checkTagContent($phpcsFile,'copyright',$tags);
    parent::processCopyright($phpcsFile,$tags);
  }

  protected function processLicense($phpcsFile, array $tags)
  {
    $this->_checkTagContent($phpcsFile,'license',$tags);
    parent::processLicense($phpcsFile,$tags);
  }

  protected function processVersion($phpcsFile, array $tags)
  {
    $this->_checkTagContent($phpcsFile,'version',$tags);
    parent::processVersion($phpcsFile,$tags);
  }
}
