<?php
$x='a'; //false
$a->$x=3; //false, false
A::$x=4; //true

class X
{
  public $a; //false

  public function asfd($x) //false
  {
    $a='b'; //false
    $this->$a=2; //false, false
    self::$a=true; //true
  }
}
