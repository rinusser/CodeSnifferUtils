<?php
declare(strict_types=1);
/**
 * requires PHP version 7.0+
 * @author Richard Nusser
 * @copyright 2017 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 * @link https://github.com/rinusser/CodeSnifferUtils
 */

namespace RN\CodeSnifferUtils\Utils;

use PHP_CodeSniffer\Files\File;

/**
 * Class supplying debugging features. Do not use in production code.
 */
abstract class Debug
{
  /**
   * Dumps a range of tokens from list, given by start and end indices
   *
   * @param array       $tokens the list of tokens
   * @param int         $start  the first token index to output
   * @param int         $end    the last token index to output
   * @param string|NULL $title  an optional title to print
   * @return void
   */
  public static function dumpTokenRange(array $tokens, int $start, int $end, ?string $title=NULL): void
  {
    $stringifier=function($x) {
      return preg_replace("/\n */",'',var_export($x,true));
    };
    $rows=array_map($stringifier,array_slice($tokens,$start,$end-$start+1));
    if($title)
      fprintf(STDERR,"\n%s",$title);
    fprintf(STDERR,"\n  %s\n",implode("\n  ",$rows));
  }

  /**
   * Dumps file details
   *
   * @param File $file the file to dump
   * @return void
   */
  public static function dumpFile(File $file): void
  {
    self::dumpTokenRange($file->getTokens(),0,$file->numTokens,$file->getFilename());
  }

  /**
   * Dumps tokens on a given line
   *
   * @param array       $tokens the tokens to search
   * @param int         $line   the line number to dump
   * @param string|NULL $title  (optional) a description to output
   * @return void
   */
  public static function dumpTokensOnLine(array $tokens, int $line, ?string $title=NULL): void
  {
    $start=-1;
    $end=count($tokens);
    foreach($tokens as $ti=>$token)
    {
      if($start<0 && $token['line']==$line)
        $start=$ti;
      if($token['line']>$line)
      {
        $end=$ti-1;
        break;
      }
    }
    if($start>=0)
      self::dumpTokenRange($tokens,$start,$end,$title);
  }

  /**
   * Dumps a list of phpcs tokens surrounding a given main token
   *
   * @param array       $tokens    the list of tokens
   * @param int         $stack_ptr the main token's offset
   * @param int         $radius    how many tokens to output on either side in addition
   * @param string|NULL $title     an optional title to print
   * @return void
   */
  public static function dumpToken(array $tokens, int $stack_ptr, int $radius=1, ?string $title=NULL): void
  {
    if($title)
      fprintf(STDERR,"\n%s:",$title);
    self::dumpTokenRange($tokens,$stack_ptr-$radius,$stack_ptr+$radius);
  }

  /**
   * Outputs a value, attempts to convert it to string
   *
   * @param mixed $what the value to output
   * @return void
   */
  public static function output($what): void
  {
    if($what instanceof Exception)
      $output=$what->getMessage()."\n".$what->getTraceAsString();
    elseif(is_object($what))
      $output=$what->__toString();
    else
      $output=(string)$what;
    fprintf(STDERR,"\n%s\n",$what);
  }
}
