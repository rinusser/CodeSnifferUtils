<?php
declare(strict_types=1);
/**
 * requires PHP version 7.1+
 * @author Richard Nusser <do.not@con.tact>
 * @copyright 2017 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 */

namespace RN\CodeSnifferUtils\Sniffs\Commenting;

/**
 * Helper class to parse and handle sniffs' property strings
 */
abstract class CommentSniffConfigurator
{
  /**
   * Parses the configured "required tags" setting into the sniff's internal tag config
   *
   * @param ?string $required_tags the tag string; NULL to keep defaults, empty string to require no tags
   * @param array   $tags          a reference to the internal tag config - will be updated!
   * @return null
   */
  public static function parseRequiredTags(?string $required_tags, array &$tags): void
  {
    if($required_tags===NULL)
      return;

    //assemble list of required tags
    $required=[];
    foreach(explode(',',$required_tags) as $tag)
    {
      if(!$tag)
        continue;
      if(substr($tag,0,1)!='@')
        $tag='@'.$tag;
      $required[]=$tag;
    }

    //update known tags' "required" flag
    foreach($tags as $name=>$options)
    {
      $is_required=in_array($name,$required);
      if($is_required)
        $required=array_diff($required,[$name]);
      $tags[$name]['required']=$is_required;
    }

    //register unknown tags
    foreach($required as $tag)
      $tags[$tag]=['required'=>true,'allow_multiple'=>false];
  }
}
