<?php
declare(strict_types=1);
/**
 * requires PHP version 7.0+
 * @author Richard Nusser
 * @copyright 2017 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 */

namespace RN\CodeSnifferUtils\Sniffs\Commenting;

use PHP_CodeSniffer\Files\File;

/**
 * Checks tag content, use in file/class comment sniffs
 */
trait CheckTagContent
{
  public $categoryContent=NULL;
  public $packageContent=NULL;
  public $subpackageContent=NULL;
  public $authorContent=NULL;
  public $copyrightContent=NULL;
  public $licenseContent=NULL;
  public $versionContent=NULL;


  protected function _checkTagContent(File $phpcsFile, string $tagname, array $offsets): void
  {
    $property_name=$tagname.'Content';
    if(!$offsets || empty($this->$property_name))
      return;
    $expectation=$this->$property_name;
    $docblock_start=$phpcsFile->findPrevious(T_DOC_COMMENT_OPEN_TAG,$offsets[0],NULL,false);
    $tokens=$phpcsFile->getTokens();
    $tag_boundaries=$tokens[$docblock_start]['comment_tags'];
    $tag_boundaries[]=$tokens[$docblock_start]['comment_closer'];
    $tag_ranges=[];
    foreach($tag_boundaries as $ti=>$start)
    {
      if(!isset($tag_boundaries[$ti+1]))
        break;
      $tag_ranges[$start]=$tag_boundaries[$ti+1]-$start-1;
    }

    foreach($offsets as $offset)
    {
      $content=trim($phpcsFile->getTokensAsString($offset+1,$tag_ranges[$offset]-2));
      if($content===$expectation)
        continue;
      $error="@$tagname tag content doesn't match configured content";
      $phpcsFile->addError($error,$offset,'Wrong'.ucfirst($tagname).'Content');
    }
  }
}
