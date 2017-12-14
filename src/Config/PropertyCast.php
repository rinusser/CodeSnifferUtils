<?php
declare(strict_types=1);
/**
 * requires PHP version 7.0+
 * @author Richard Nusser
 * @copyright 2017 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 * @link https://github.com/rinusser/CodeSnifferUtils
 */

namespace RN\CodeSnifferUtils\Config;

/**
 * Converts property strings (as set in XML configuration files) to PHP types
 */
abstract class PropertyCast
{
  private static $_boolValues=[[true, ['true', 'yes','1','y']],
                               [false,['false','no', '0','n']]];

  /**
   * Parses a boolean property
   *
   * @param mixed  $raw_value     the value to cast to bool
   * @param string $property_name the property name to use in error messages
   * @return bool the parsed boolean
   * @throws InvalidArgumentException if the value can't be parsed
   */
  public static function toBool($raw_value, string $property_name): bool
  {
    if(is_bool($raw_value))
      return $raw_value;

    $value=trim(strtolower($raw_value));
    foreach(self::$_boolValues as [$rv,$values])
      if(in_array($value,$values))
        return $rv;

    throw new \InvalidArgumentException('invalid '.$property_name.' property value "'.$raw_value.'"');
  }
}
