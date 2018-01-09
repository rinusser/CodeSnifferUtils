<?php
declare(strict_types=1);
/**
 * requires PHP version 7.1+
 * @author Richard Nusser
 * @copyright 2017-2018 Richard Nusser
 * @license GPLv3 (see http://www.gnu.org/licenses/)
 * @link https://github.com/rinusser/CodeSnifferUtils
 */

namespace RN\CodeSnifferUtils\Config;

use RN\CodeSnifferUtils\Utils\NoImplicitProperties;

/**
 * Per-file sniff configuration model
 */
class FileConfig
{
  use NoImplicitProperties;

  public $disabledSniffs=[];
}
