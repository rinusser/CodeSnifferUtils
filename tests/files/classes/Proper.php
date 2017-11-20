<?php

function asdf()
{
  $x=2;
}

$y=1;


abstract class CorrectOrder
{
  const C1=NULL;
  const C2=1;

  const C3=2;

  protected static $_staticProperty1;
  protected static $_staticProperty2;

  protected static $_staticProperty3;


  public static function staticMethod1($shouldBeIgnored)
  {
    $z=1;
  }

  protected static function _staticMethod2()
  {
  }

  private $_instanceProperty1;
  protected $_instanceProperty2;


  public function instanceMethod1()
  {
  }

  abstract public function instanceMethod2();

  public function instanceMethod3(string $has, $args)
  {
    $a=1;
  }
}
