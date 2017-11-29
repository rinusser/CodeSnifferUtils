<?php

class ProperNaming
{
  public static $some;
  public static $somePublic;
  public static $somePublicProperty1;

  protected static $_staticProperty2;
  protected static $_allowed;

  private static $_staticProperty9;
  private static $_someID;


  public $property1;
  public $proPerTy2;

  protected $_property3;
  protected $_properTy4;

  private $_property;

  public static $_IgnoredName; //CSU.IgnoreName
  private static $thistoo; //CSU.IgnoreName
}

trait ProperlyNamedTrait
{
  public $traitProperty;
  protected $_anotherTraitProperty;
}


function this_is_a_function() {}

function too2() {}

class ProperMethodNaming
{
  public static function thisShouldWork() {}
  protected static function _soShouldThis1() {}
  private static function _and() {}
  public function publicMethods() {}
  protected function _must2() {}
  private function _orElse() {}
}

trait ProperTraitMethodNaming
{
  public static function iReally() {}
  protected function _hope() {}
  private function _thisWorks() {}
}


class IgnoredMethodNaming
{
  public static function _thisShouldBeIgnored() {} //CSU.IgnoreName
  protected function __a_s_d_f() {} //CSU.IgnoreName
}