<?php
declare(strict_types=1);
/**
 * Requires PHP version 7.1+
 * @author Richard Nusser
 * @copyright 2017 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 * @link https://github.com/rinusser/CodeSnifferUtils
 */

namespace RN\CodeSnifferUtils\Tests;

/**
 * phpcs testcase, contains all data needed to perform a test
 */
class XMLTestCase
{
  private $_filename;
  private $_sources;
  private $_expectedFileCount;
  private $_expectedErrors;
  private $_skip;


  /**
   * @param string $filename            the .xml's filename
   * @param array  $sources             the list of source entries, can be files or directories
   * @param int    $expected_file_count the number of files expected to be parsed
   * @param array  $expected_errors     the list of expected errors
   */
  public function __construct(string $filename, array $sources, int $expected_file_count, array $expected_errors)
  {
    $this->_filename=$filename;
    $this->_sources=$sources;
    $this->_expectedFileCount=$expected_file_count;
    $this->_expectedErrors=$expected_errors;
  }


  /**
   * @param string $name  the accessed property's name
   * @param mixed  $value the value to write to the property
   * @return mixed returns $value to be consistent with actually defined properties
   * @throws LogicException if propery doesn't exist
   */
  public function __set($name, $value)
  {
    $this->_checkProperty($name,'write to');
    $this->{'_'.$name}=$value;
    return $value;
  }

  /**
   * @param string $name the accessed property's name
   * @return mixed the property's value
   * @throws LogicException if propery doesn't exist
   */
  public function __get($name)
  {
    $this->_checkProperty($name,'read from');
    return $this->{'_'.$name};
  }

  private function _checkProperty(string $name, string $access)
  {
    if(!array_key_exists('_'.$name,get_object_vars($this)))
      throw new \LogicException('cannot '.$access.' nonexistant property "'.$name.'"');
  }
}
