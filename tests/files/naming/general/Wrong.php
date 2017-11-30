<?php

class WrongNaming
{
  public static $_staticProperty1;
  public static $StaticProperty2;
  public static $staticProperty3;
  public static $static_Property;
  public static $_static_property;
  public static $static_propertytoo;

  protected static $staticProperty4;
  protected static $_StaticProperty5;
  protected static $_staticProperty6;

  private static $staticProperty7;
  private static $_StaticProperty8;
  private static $_staticProperty9;


  public $_property1;
  public $Property2;
  public $property3;
  public $camelCaseProperty;

  protected $property4;
  protected $_Property5;
  protected $_properTy6;

  private $property7;
  private $_Property8;
  private $_property9;
  private $_snake_case;
}

trait WronglyNamedTrait
{
  public static $_traitProperty;
  protected $traitProperty;
}


class WrongMethodNaming
{
  public static function NoLeadingUppercase() {}
  public function _extraLeadingUnderscore() {}
  public function snake_case() {}

  protected static function missingLeadingUnderscore() {}
  private function missing2() {}
  private function _snake_case() {}
}

trait WrongTraitMethodNaming
{
  public static function _pleaseBreak() {}
  protected function _IneedThis() {}
  private function really() {}
}

function thisIsWrong() {}
function asShouldThis() {}
function Afunction() {}
function _no_leading_underscores() {}


class WrongParameterNaming
{
  public function a(int &$_somePar) {}
  private function _b($A) {}
}

trait WrongParameterNamingTrait
{
  protected function _c($_a_b) {}
}

function x($A, $_b, int $somePar=3) {}
$y=function($myPar,$_find_this_please) {};


class WrongVariableNaming
{
  public function asdf()
  {
    $xY=4;
    $_a=2;
    list($aA,$_b)=$_SERVER['argv'];
    $_NOSUCHGLOBALEXISTS=2;

    $y=function() { $Invalid=1; }
  }
}
