<?php
declare(strict_types=1);
/**
 * requires PHP version 7.0+
 * @author Richard Nusser
 * @copyright 2017 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 * @link https://github.com/rinusser/CodeSnifferUtils
 */

namespace RN\CodeSnifferUtils\Standards\RN\Sniffs\Commenting;

use PHP_CodeSniffer\Standards\PEAR\Sniffs\Commenting\ClassCommentSniff;
use PHP_CodeSniffer\Files\File;
use RN\CodeSnifferUtils\Config\PerFileSniffConfig;
use RN\CodeSnifferUtils\Utils\NoImplicitProperties;

/**
 * This is based on PEAR.Commenting.ClassComment, adds configurable features
 */
class ConfigurableClassCommentSniff extends ClassCommentSniff
{
  //include requireAuthorEmail property and handling
  use RequireAuthorEmail;

  //include expected content properties and content checker
  use CheckTagContent;

  //include config handling
  use PerFileSniffConfig;

  //disallow access to undeclared properties
  use NoImplicitProperties;


  public $requiredTags=NULL;
  protected $currentFile;  //CSU.IgnoreName: required by parent class


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

    CommentSniffConfigurator::parseRequiredTags($this->requiredTags,$this->tags);
    $rv=parent::process($file,$stack_ptr);
    $this->_insertFixables($file,$this->_fixables);
    return $rv;
  }

  protected function processTags($file, $stack_ptr, $comment_start)  //CSU.IgnoreName: required by parent class
  {
    $this->_processInsertableTags($file,$comment_start,$this->tags,'class');
    return parent::processTags($file,$stack_ptr,$comment_start);
  }

  protected function processCategory($file, array $tags)  //CSU.IgnoreName: required by parent class
  {
    $this->_checkTagContent($file,'category',$tags);
    parent::processCategory($file,$tags);
  }

  protected function processPackage($file, array $tags)  //CSU.IgnoreName: required by parent class
  {
    $this->_checkTagContent($file,'package',$tags);
    parent::processPackage($file,$tags);
  }

  protected function processSubpackage($file, array $tags)  //CSU.IgnoreName: required by parent class
  {
    $this->_checkTagContent($file,'subpackage',$tags);
    parent::processSubpackage($file,$tags);
  }

  protected function processAuthor($file, array $tags)  //CSU.IgnoreName: required by parent class
  {
    $this->_checkTagContent($file,'author',$tags);
    $unhandled=$this->_processAuthorWrapper($file,$tags);
    if($unhandled)
      parent::processAuthor($file,$unhandled);
  }

  protected function processCopyright($file, array $tags)  //CSU.IgnoreName: required by parent class
  {
    $this->_checkTagContent($file,'copyright',$tags);
    parent::processCopyright($file,$tags);
  }

  protected function processLicense($file, array $tags)  //CSU.IgnoreName: required by parent class
  {
    $this->_checkTagContent($file,'license',$tags);
    parent::processLicense($file,$tags);
  }

  protected function processVersion($file, array $tags)  //CSU.IgnoreName: required by parent class
  {
    $this->_checkTagContent($file,'version',$tags);
    parent::processVersion($file,$tags);
  }

  protected function processLink($file, array $tags)  //CSU.IgnoreName: required by parent class
  {
    $this->_checkTagContent($file,'link',$tags);
  }
}
