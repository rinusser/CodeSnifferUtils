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
class Proper
{
  public static $staticMember;

  protected static $_firstLine;
  protected static $_secondLine;

  private $_member;

  public function prim()
  {
    $x=1;
    function() use ($x) {}();
  }
}

class PropertyWithComment
{
  //asdf
  //asdf2
  public $noComment;

  //sdfg
  //tons of more info
  protected $_actualInfo;
  //protected $_aBunchOf;
  //protected $_unnecessary;
  //protected $_properties;
  protected $_important;
}

class PropertyWithDocBlock
{
  /**
   * @var int any number
   */
  public $num;

  /**
   * @var string text
   */
  public $text;
}

class ConstWithoutComments
{
  const A='a';
  const B='b';
}

class ConstWithModifier
{
  public const PUB='foo';
  const DEF='bar';

  protected const PROT='baz';
  private const PRIV='oof';
}

class ConstWithComment
{
  //some
  //comments
  const C='c';
  //const C1=1
  const D='d';
  //const D2=1;
  //const D3=1;
  const E='e';

  //const E1=1;
  //const E2=1;
  //const E3=1;
  const F='f';
}

class ConstWithDocBlock
{
  /**
   * important const
   */
  const G='g';

  /**
   * not so important const
   */
  protected const H='h';

  /**
   * "thanks for participating"-award winning const
   */
  private const I='i';
}


class MethodWithoutComment
{
  public function imALittleMethod()
  {
  }
}

class MethodWithComment
{
  //some
  //important
  //stuff
  public function withComment()
  {
  }


  //more
  //comments
  public function moreComments()
  {
  }
}

class MethodWithDocBlock
{
  /**
   * it's a method, yo!
   */
  public function withDocBlock()
  {
  }

  /**
   * similar method
   */
  public function similarMethod()
  {
  }


  /**
   * more space
   */
  public function moreSpace()
  {
  }
}

abstract class AbstractMethods
{
  abstract public function thisShould(): void;
  abstract protected function work();

  abstract protected function orElse();


  abstract protected function illBeSad();


  protected function andCry()
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


/**
 * docblock
 */
class ClassWithDocBlock
{
}


//commented out "use" statement


class ClassWithNearbyComment
{
}


function properly_spaced_parameters($a=1, $b)
{
}

class MethodParameterWithDefault
{
  function x($c=$def, $d=$def){}

  function y() {}

  function withTypeHint(string $str, callable $func, ?\StdClass $obj=$def) {}

  function withLastDefault($a, $b=1)
  {
    $x=2;
  }

  abstract function overMultipleLines($a,
                                      $b,
                                      $c);
}


class ClassImportingTraits
{
  use Trait1;
  use Trait2;

  //currently importing multiple traits requires a comment to allow an empty line in between
  use Trait3;
  //use Trait4;

  //use Trait5;
  use Trait6;
}
