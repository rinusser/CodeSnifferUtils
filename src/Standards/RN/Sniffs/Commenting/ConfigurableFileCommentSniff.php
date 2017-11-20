<?php
declare(strict_types=1);
/**
 * requires PHP version 7.0+
 * @author Richard Nusser <do.not@con.tact>
 * @copyright 2017 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 */

namespace PHP_CodeSniffer\Standards\RN\Sniffs\Commenting; //XXX phpcs's property injection doesn't work for sniffs in foreign namespaces

use PHP_CodeSniffer\Standards\PEAR\Sniffs\Commenting\FileCommentSniff;
use PHP_CodeSniffer\Files\File;

/**
 * This is pretty much PEAR.Commenting.FileComment, just with a configurable list of required tags
 */
class ConfigurableFileCommentSniff extends FileCommentSniff
{
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
}
