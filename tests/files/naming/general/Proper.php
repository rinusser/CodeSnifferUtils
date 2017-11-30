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


abstract class ProperParameterNaming
{
  abstract public static function a($come, int $get, \StdClass &...$some);
  private function _a($this_is_valid=3)
  {
    $x=function($a,$b) {};
  }
}

trait ProperParameterNamingTrait
{
  private function _x($validate_me) {}
}

function abc($a, $b_c) {};
$y=function($x=3,$y_z=5) {};


class ProperVariableNaming
{
  public function a()
  {
    $a_b_c3=$_SERVER['hostname'].$_GET['idx'];
    SomeClass::$a_b_c3;
    self::$_X=1;
    list[$a_b,$c]=array_values($_SERVER);
  }
}

$a_valid_variable_name_2='y';
$$a_valid_variable_name_2(2,1);
