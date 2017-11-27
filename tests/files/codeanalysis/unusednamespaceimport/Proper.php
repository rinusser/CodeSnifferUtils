<?php

use A;
use A\B;
use A\{C,D};
use E\F as G;

$a=2;
$b=function() use ($a) {};

class X extends B implements A
{
}

class Y extends C
{
  use SomeTrait;

  public function __construct()
  {
    new D();
  }

  public function something()
  {
    $a=1;
    $x=function() use ($a) {};
    return G::className;
  }
}
