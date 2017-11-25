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

class TooMuchSpace
{

  public static $pub;



  protected $_prot;






}

class TooMuchSpaceWithDocBlock
{

  /**
   * some var
   */
  protected $_var;
}

class TooMuchSpaceWithOneLineComment
{

  //whatever
  private $_asdf;
}


class ConstWithoutComments
{

  const NO_CLASS_PADDING='z';



  const WAY_TOO_LATE='y';
}

class ConstWithOneLiner
{

  //asdf
  const NOPE='nah';
}

class ConstWithMultipleOneLiners
{

  //should
  //not
  //pass
  const I_HOPE='dearly';



  //spacey
  const NOT_KEVIN=NULL;
}

class ConstWithDocBlocks
{

  /**
   * this is a constant
   */
  const DUH=3;



  /**
   * this is a constant too!
   */
  protected const TSNOC='eulav';
}


class Methods
{

  public static function noComment()
  {
  }



  public function asdf()
  {
  }
}

abstract class MethodsWithComments
{

  //asdf
  public function with()
  {
  }



  //fdsa
  abstract public function comment();



  //xx
  //asdf
  protected function again()
  {
  }
}

abstract class MethodsWithDocBlocks
{

  /**
   *
   */
  public function doc()
  {
  }



  /**
   *
   */
  abstract protected function blocks();



  /**
   *
   */
  protected function work()
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



class EmptyClassWithPadding()
{

}

class ClassWithPadding()
{
  function asdf()
  {
  }

}



abstract class AbstractWithTooMuchSpace()
{
  const C1=NULL;


  const C2=NULL;
}


function too_far_apart( $a,  $b= $c) {}

abstract class MethodParameters
{
  abstract public function tooFarApart( $d= $x,  $e);

  public function withTypeHints( \StdClass  $obj, ?int $number) {}

  abstract protected function withMultilineTypeHints(int  $x,
                                                     \Exception  $e);

  abstract public function withFirstEllipsis( ...$x);
  abstract public function withLaterEllipsis($a,  ...$x);
  abstract public function withFirstEllipsisAndTypeHint( string ...$y);
  abstract public function withLaterEllipsisAndTypeHint($x,  string ...$y);
  abstract public function withEllipsisAndTypeHint(string  ...$y);
}

?>
<?php


function too_much_space_after_php_tag()
{
}

?>
<?php


const TOO_MUCH_SPACE_AFTER_PHP_TAG=2;
