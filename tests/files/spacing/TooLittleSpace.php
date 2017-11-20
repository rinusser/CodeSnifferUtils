<?php
declare(strict_types=1);
/**
 * file comment
 */
namespace A\B;
use A\C;
use B\{D,E};
/**
 * class comment
 */
class Improper
{
  const WE='are';
  public static $way;
  public $too;
  const CLOSE='together';
  public function even()
  {
  }
  //comments
  public function shouldNot()
  {
  }
  //allow
  //for
  //multiple
  protected function methods()
  {
  }
  /**
   * to
   */
  private function clump()
  {
  }
}
function functionWithoutComment()
{
}
//
//
function functionWithOneLineComments()
{
}
/**
 * docblock
 */
function functionWithDocBlock()
{
}
class A
{
}
class B
{
}

function too_close($a,$b,$c) {}

class MethodParameterSpacing
{
  public function tooClose($a,$b=$a) {}
}
