<?php
declare(strict_types=1);
/**
 * requires PHP version 7.1+
 * @author Richard Nusser
 * @copyright 2017 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 * @link https://github.com/rinusser/CodeSnifferUtils
 */

namespace RN\CodeSnifferUtils\Standards\RN\Sniffs\Commenting;

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
  public $linkContent=NULL;
  public $seeContent=NULL;
  public $sinceContent=NULL;
  public $deprecatedContent=NULL;

  protected $_fixables=[];


  protected function _checkTagContent(File $file, string $tagname, array $offsets): void
  {
    $property_name=$tagname.'Content';
    if(!$offsets || empty($this->$property_name))
      return;
    $expectation=$this->$property_name;
    $docblock_start=$file->findPrevious(T_DOC_COMMENT_OPEN_TAG,$offsets[0],NULL,false);
    $tokens=$file->getTokens();
    $tag_boundaries=$tokens[$docblock_start]['comment_tags'];
    $tag_boundaries[]=$tokens[$docblock_start]['comment_closer'];
    $tag_sizes=$this->_assembleTagSizes($tokens,$tag_boundaries);

    foreach($offsets as $offset)
    {
      $content=$file->getTokensAsString($offset+2,$tag_sizes[$offset]);
      if($content===$expectation)
        continue;
      $error="@$tagname tag content doesn't match configured content";
      $fix=$file->addFixableError($error,$offset,'Wrong'.ucfirst($tagname).'Content');
      if($fix)
      {
        $file->fixer->beginChangeset();

        //replace first text after tag with expectected content
        $file->fixer->replaceToken($offset+2,$expectation);

        //remove the rest of the line
        for($ti=1;$ti<$tag_sizes[$offset];$ti++)
          $file->fixer->replaceToken($offset+2+$ti,'');

        $file->fixer->endChangeset();
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

  protected function _processInsertableTags(File $file, int $comment_start, array &$tags, string $type): void
  {
    $tokens=$file->getTokens();
    $docblock=$tokens[$comment_start];
    $fixables=[];

    foreach($tags as $tag=>$options)
    {
      $expected_content=$this->{ltrim($tag,'@').'Content'}??NULL;
      if(!$options['required'] || $expected_content===NULL)
        continue;

      foreach($docblock['comment_tags'] as $offset)
        if($tokens[$offset]['content']===$tag)
          continue 2;

      $error='Missing '.$tag.' in '.$type.' comment';
      if($file->addFixableError($error,$docblock['comment_closer'],'InsertableMissing'.ucfirst(ltrim($tag,'@')).'Tag'))
        $fixables[]=[$comment_start,$tag,$expected_content];
      $tags[$tag]['required']=false;
    }
    $this->_fixables=array_reverse($fixables);
  }

  protected function _insertFixables(File $file, array $fixables): void
  {
    if(!$fixables)
      return;
    $file->fixer->beginChangeset();
    foreach($fixables as $fixable)
    {
      [$comment_start,$tag,$expected_content]=$fixable;
      $insert_before=$this->_calculateInsertTagFollower($file,$comment_start,$tag);
      $line_start=$file->findFirstOnLine([],$insert_before,true);
      $prefix=' ';
      $file->fixer->addContentBefore($line_start,"$prefix* $tag $expected_content\n");
    }
    $file->fixer->endChangeset();
  }

  private function _calculateInsertTagFollower(File $file, int $comment_start, string $tag): int
  {
    $tokens=$file->getTokens();
    $expected_order=array_keys($this->tags);
    $pos=array_search($tag,$expected_order);
    if($pos!==false)
      foreach(array_slice($expected_order,$pos+1) as $candidate)
        foreach($tokens[$comment_start]['comment_tags'] as $offset)
          if($tokens[$offset]['content']===$candidate)
            return $offset;
    return $tokens[$comment_start]['comment_closer'];
  }
}
