<?php

abstract class Alternative
{
  abstract public function instanceMethod1();
  abstract public function instanceMethod2();

  private $_instanceProperty1;
  private $_instanceProperty2;

  public static function staticMethod1() {}
  public static function staticMethod2() {}

  protected static $_staticProperty1;
  protected static $_staticProperty2;

  const C1=NULL;
  const C2=NULL;
}
