<?php
declare(strict_types=1);
/**
 * requires PHP version 7.0+
 * @author Richard Nusser
 * @copyright 2017 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 * @link https://github.com/rinusser/CodeSnifferUtils
 */

namespace RN\CodeSnifferUtils\Standards\RN\Sniffs\Naming;

use PHP_CodeSniffer\Files\File;

/**
 * Helper functions to make sure symbols are named properly
 */
abstract class NameChecker
{
  /**
   * Determines whether the naming checks should be ignored for this symbol
   * Currently this is being done with a special  //CSU.IgnoreName  comment in the same line
   *
   * @param File $file      the phpcs file to check
   * @param int  $stack_ptr the token offset to check
   * @return bool whether the token is skipped
   */
  public static function isSkipped(File $file, int $stack_ptr): bool
  {
    $tokens=$file->getTokens();
    $start_line=$tokens[$stack_ptr]['line'];

    for($offset=$stack_ptr+1;;$offset++)
    {
      if(!isset($tokens[$offset]) || $tokens[$offset]['line']!=$start_line)
        break;
      if($tokens[$offset]['code']===T_COMMENT && strpos($tokens[$offset]['content'],'CSU.IgnoreName')!==false)
        return true;
    }
    return false;
  }

  /**
   * Checks phpcs files for visibility prefix naming
   *  - public properties must not start with an underscore
   *  - private/protected properties must start with an underscore
   *  - after the leading underscore (or not), properties must start with a lowercase letter and not contain any other underscores
   *
   * @param File   $file           the phpcs file handle being checked
   * @param int    $stack_ptr      the token offset to apply any errors to
   * @param string $visibility     the class member's visibility
   * @param string $type           the symbol type to use in error messages, e.g. "property"
   * @param string $name           the class member's visibility
   * @param string $displayed_name the name to display in error messages
   * @return bool whether the class member is valid: true if it is, false if errors have been found
   */
  public static function checkUnderscorePrefix(File $file, int $stack_ptr, string $visibility, string $type, string $name, string $displayed_name): bool
  {
    $has_leading_underscore=$name[0]==='_';
    $should_have_leading_underscore=$visibility!=='public';

    if($has_leading_underscore!==$should_have_leading_underscore)
    {
      $error=ucfirst($visibility).' %s "%s" should '.($should_have_leading_underscore?'':'not ').'start with an underscore';
      $code=$should_have_leading_underscore?'NonPublicUnderscoreMissing':'PublicHasUnderscore';
      $file->addError($error,$stack_ptr,$code,[$type,$displayed_name]);
      return false;
    }

    if($has_leading_underscore)
      $name=substr($name,1);

    if(!ctype_lower($name[0]))
    {
      $error='%s "%s" should start with a lowercase character';
      $file->addError($error,$stack_ptr,'InvalidStart',[ucfirst($type),$displayed_name]);
      return false;
    }

    if(strpos($name,'_')!==false)
    {
      $error='%s "%s" should not contain underscores after the start of the name';
      $file->addError($error,$stack_ptr,'UnderscoreAfterStart',[ucfirst($type),$displayed_name]);
      return false;
    }

    return true;
  }

  /**
   * Checks whether a name is in snake_case.
   * This method doesn't check whether a name starts with leading underscores, handle this separately.
   *
   * @param File        $file           the phpcs file handle to check
   * @param int         $stack_ptr      the token offset to put any errors on
   * @param string      $type           the token type, for use in the error message
   * @param string      $name           the name to validate
   * @param string|NULL $displayed_name the name to use in error messages
   * @return void
   */
  public static function checkSnakeCase(File $file, int $stack_ptr, string $type, string $name, ?string $displayed_name=NULL): void
  {
    if(!$displayed_name)
      $displayed_name=$name;

    if(!preg_match('/^[a-z0-9_]+$/',$name))
    {
      $error='%s "%s" must be in snake_case - only lowercase letters, numbers and underscores are allowed';
      $file->addError($error,$stack_ptr,'InvalidCharacters',[ucfirst($type),$displayed_name]);
    }
  }
}
