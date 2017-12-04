<?php

class ModelClass
{
  public $x;
}

class EmptyClass
{
}

class MixedPropertyClass
{
  public $instanceProperty;
  public static $staticProperty;
}

abstract class AbstractMixedPropertyClass
{
  public $instanceProperty;
  public static $staticProperty;
}

abstract class AbstractStaticClass
{
  const SOME_CONST=1;
  public static $staticProperty;
  public static function staticMethod() {}
}

trait TraitsShouldBeIgnored
{
  const SOME_CONST=1;
  public static $staticProperty;
  public static function staticMethod() {}
}

class UseShouldntRequireAbstract
{
  const SOME_CONST=1;
  public static $staticProperty;
  use SomeTrait;
  public static function staticMethod() {}
}

class InstanceMethod
{
  const SOME_CONST=1;
  public static $staticProperty;
  public static function staticMethod() {}
  public function instanceMethod() {}
}

class ConstsOnly
{
  const SOME_CONST=1;
}

class Extending extends X
{
  public static $staticProperty;
}
