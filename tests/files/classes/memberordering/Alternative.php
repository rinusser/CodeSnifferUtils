<?php

abstract class Alternative
{
  abstract public function instanceMethod1();
  abstract public function instanceMethod2();

  private $_instanceProperty1;
  private $_instanceProperty2;

  public static function staticMethod1() {}
  public static function staticMethod2() {}

  public function __construct()
  {
    $y=1;
    $this->_instanceProperty1=function() use ($y) {return $y;}; //this shouldn't be considered when looking for "use" in class
  }

  protected static $_staticProperty1;
  protected static $_staticProperty2;

  use Trait1;
  use Trait2;

  const C1=NULL;
  const C2=NULL;
}
