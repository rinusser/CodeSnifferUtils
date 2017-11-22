<?php
declare(strict_types=1);
/**
 * requires PHP version 7.0+
 * @author Richard Nusser
 * @copyright 2017 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 */

namespace PHP_CodeSniffer\Standards\RN\Sniffs\Commenting; //XXX phpcs's property injection doesn't work for sniffs in foreign namespaces

use PHP_CodeSniffer\Standards\PEAR\Sniffs\Commenting\ClassCommentSniff;
use PHP_CodeSniffer\Files\File;
use RN\CodeSnifferUtils\Sniffs\Commenting\RequireAuthorEmail;

/**
 * This is pretty much PEAR.Commenting.ClassComment, just with a configurable list of required tags
 */
class ConfigurableClassCommentSniff extends ClassCommentSniff
{
  //include requireAuthorEmail property and handling
  use RequireAuthorEmail;

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

  protected function processAuthor($phpcsFile, array $tags)
  {
    $unhandled=$this->_processAuthorWrapper($phpcsFile,$tags);
    if($unhandled)
      parent::processAuthor($phpcsFile,$unhandled);
  }
}
