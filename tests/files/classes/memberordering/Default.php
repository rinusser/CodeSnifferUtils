<?php

abstract class DefaultOrder
{
  const C1=NULL;

  protected static $_staticProperty1;

  public static function staticMethod1() {}

  private $_instanceProperty1;

  private function __construct() {}

  abstract public function instanceMethod1();
}
