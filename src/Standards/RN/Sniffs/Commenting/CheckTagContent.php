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
    $tag_sizes=$this->_assembleTagSizes($tokens,$tag_boundaries);

    foreach($offsets as $offset)
    {
      $content=$phpcsFile->getTokensAsString($offset+2,$tag_sizes[$offset]);
      if($content===$expectation)
        continue;
      $error="@$tagname tag content doesn't match configured content";
      $fix=$phpcsFile->addFixableError($error,$offset,'Wrong'.ucfirst($tagname).'Content');
      if($fix)
      {
        $phpcsFile->fixer->beginChangeset();

        //replace first text after tag with expectected content
        $phpcsFile->fixer->replaceToken($offset+2,$expectation);

        //remove the rest of the line
        for($ti=1;$ti<$tag_sizes[$offset];$ti++)
          $phpcsFile->fixer->replaceToken($offset+2+$ti,'');

        $phpcsFile->fixer->endChangeset();
      }
    }
  }

  /**
   * Turns the list of phpdoc tag token offsets into an array of start=>length entries for the tags' contents
   *
   * @param array $tokens         the phpcs file tokens to look through
   * @param array $tag_boundaries the list of tag token offsets, plus a final delimiter (e.g. end of the docblock)
   * @return array a list of start=>length entries
   */
  private function _assembleTagSizes(array $tokens, array $tag_boundaries): array
  {
    $rv=[];
    foreach($tag_boundaries as $ti=>$start)
    {
      //last boundary entry is actually end of the docblock
      if(!isset($tag_boundaries[$ti+1]))
        break;

      $rv[$start]=0;
      for($offset=$start+2;$offset<$tag_boundaries[$ti+1];$offset++)
      {
        if($tokens[$offset]['line']!=$tokens[$start]['line'])
          break;
        $rv[$start]++;
      }

      //skip trailing newline token
      if($rv[$start]>0)
        $rv[$start]--;
    }
    return $rv;
  }
}
