<?php
declare(strict_types=1);
/**
 * requires PHP version 7.0+
 * @author Richard Nusser
 * @copyright 2017 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 */

namespace RN\CodeSnifferUtils\Utils;

/**
 * Class holding phpcs token names
 */
abstract class TokenNames
{
  private static $_names=[T_OPEN_TAG=>'PHP opening tag',
                          T_DECLARE=>'declare statement',
                          T_NAMESPACE=>'namespace declaration',
                          T_USE=>'use statement',
                          T_CLOSE_PARENTHESIS=>'closing parenthesis',
                          T_DOC_COMMENT_CLOSE_TAG=>'docblock',
                          T_COMMENT=>'comment',
                          T_CLASS=>'class',
                          T_OPEN_CURLY_BRACKET=>'opening curly bracket "{"',
                          T_CLOSE_CURLY_BRACKET=>'closing curly bracket "}"',
                          T_OPEN_PARENTHESIS=>'opening parenthesis "("',
                          T_VARIABLE=>'variable or property',
                          T_FUNCTION=>'function',
                          T_SEMICOLON=>'semicolon',
                          T_COMMA=>'comma',
                          T_EQUAL=>'equal sign',
                          T_CONST=>'constant',
                         ];

  /**
   * Uses phpcs token code and token type to find a token name suitable for user output
   * @param mixed       $code the token code
   * @param string|NULL $type the token type
   * @return string the token name
   */
  public static function getPrintableName($code, $type=NULL): string
  {
    if(is_array($code))
    {
      $type=$code['type'];
      $code=$code['code'];
    }
    return isset(self::$_names[$code])?self::$_names[$code]:$type;
  }
}
