<?php
declare(strict_types=1);
/**
 * requires PHP version 7.1+
 * @author Richard Nusser
 * @copyright 2017-2018 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 * @link https://github.com/rinusser/CodeSnifferUtils
 */

namespace RN\CodeSnifferUtils\Utils;

/**
 * Disables access to undeclared class properties
 */
trait NoImplicitProperties
{
  /**
   * Gets called by PHP when an unknown property is tested for existance, e.g. isset()
   *
   * @param string $key the property name accessed
   * @return void never returns
   * @throws LogicException always
   */
  public function __isset($key)
  {
    $this->_throwImplicitPropertiesException($key);
  }

  /**
   * Gets called by PHP when an unknown property is read from
   *
   * @param string $key the property name accessed
   * @return void never returns
   * @throws LogicException always
   */
  public function __get($key)
  {
    $this->_throwImplicitPropertiesException($key);
  }

  /**
   * Gets called by PHP when an unknown property is written to
   *
   * @param string $key   the property name accessed
   * @param mixed  $value (unused) the value tried to set
   * @return void never returns
   * @throws LogicException always
   */
  public function __set($key, $value)
  {
    $this->_throwImplicitPropertiesException($key);
  }

  private function _throwImplicitPropertiesException(string $key)
  {
    throw new \LogicException('invalid access to undeclared property "'.$key.'"');
  }
}
