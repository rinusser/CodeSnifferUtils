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
