<?php

function a($somePar)
{
  $somePar++;
  $somePar2=1;
}

trait A
{
  public $Abc;
  private $a_b_c;

  public function a($Par1, $_par2, $_WeirdlyNamedPar)
  {
    $x=$Par1+$_par2+$_WeirdlyNamedPar;
    $_invalid=2;
    $_invalid++;
    $_invalid++;
    $_invalid+=$_WeirdlyNamedPar;

    $y=function() use($_WeirdlyNamedPar) {echo $_WeirdlyNamedPar;};
  }
}

$closure=function() use ($x,$_SomeOutsideVariable) {echo $_SomeOutsideVariable;};

