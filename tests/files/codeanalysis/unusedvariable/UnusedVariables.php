<?php
function asdf(array $c1, $c3)
{
  global $c3, $c4; //$c4 unused
  $c5=1;           //$c5 unused
  [$c6,$c7]=$c1;   //$c7 unused
  list($c8)=$c1;   //$c8 unused
  array(,$c9)=$c1; //$c9 unused
  SomeClass::$c3=2;
  $this->$c6=3;
  foreach($c1 as $c10=>$c11) {}
  foreach($c1 as $c12=>[$c13,$c14]) {}

  $c15=1;
  function() use ($c15) {};  //$c15 unused
  $c1[0]+=$c15;

  return $c3;
}


$f1=function() use ($d2) {
  $c16=[];
  $c16[]=3;
};
