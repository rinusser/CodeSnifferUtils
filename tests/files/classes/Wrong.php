<?php

function asdf()
{
  $x=2;
}

$y=1;


abstract class WrongOrder
{
  const C1=NULL;
  const C2=1;

  protected static $_staticProperty1;
  const C3=2;

  protected static $_staticProperty2;



  public static function staticMethod1()
  {
  }

  protected static $_staticProperty3;

  protected static function _staticMethod2()
  {
  }

  private $_instanceProperty1;


  public function instanceMethod1()
  {
  }

  protected $_instanceProperty2;

  abstract public function instanceMethod2();

  public function instanceMethod3()
  {
  }

  private static function _lateStaticMethod()
  {
  }
}
