<?php
declare(strict_types=1);
/**
 * requires PHP version 7.0+
 * @author Richard Nusser
 * @copyright 2017 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 * @link https://github.com/rinusser/CodeSnifferUtils
 */

namespace RN\CodeSnifferUtils\Sniffs\Commenting;

use PHP_CodeSniffer\Files\File;
use RN\CodeSnifferUtils\Utils\PropertyCast;

/**
 * Can make email address in author tag optional
 */
trait RequireAuthorEmail
{
  public $requireAuthorEmail=true;

  protected function _processAuthorWrapper(File $file, array $tags): array
  {
    $unhandled=[];

    $tokens=$file->getTokens();
    foreach($tags as $tag)
    {
      if($tokens[($tag+2)]['code']!==T_DOC_COMMENT_STRING)
         continue;

      $content=$tokens[($tag+2)]['content'];
      if($this->_isAuthorEmailRequired() || preg_match('/[\[\]<>()@.]/',$content))
      {
        $unhandled[]=$tag;
        continue;
      }

      if(!preg_match('/^[a-z]+[a-z0-9.\' -]+ *$/i',$content))
      {
        $error='Content of the @author tag must be in the form "Display Name"';
        $file->addError($error,$tag,'InvalidAuthorName');
      }
    }

    return $unhandled;
  }

  protected function _isAuthorEmailRequired(): bool
  {
    return PropertyCast::toBool($this->requireAuthorEmail,'requireAuthorEmail');
  }
}
