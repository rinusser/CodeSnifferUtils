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
}

trait ProperlyNamedTrait
{
  public $traitProperty;
  protected $_anotherTraitProperty;
}
