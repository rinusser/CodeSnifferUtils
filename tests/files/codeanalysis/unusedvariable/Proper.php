<?php
function asdf(array $a1, $a2) //$a2 is unused, but in a different sniff
{
  global $a3;
  $a4=1;
  [$a5,$a6]=$a1;
  list($a5)=$a1;
  SomeClass::$a5;
  $this->$a6=3;

  $a7=1;
  $a9=2;
  (function($a8) use ($a7) { return "$a7"; })($a9);

  return $a3+$a4;
}

$f1=function(&...$b1) use ($b2) {
  global $b3;
  foreach($b1 as $b4=>[$b5,$b6]) {
    yield [$b4,$b5+$b2+$b6];
  }

  $k=1;
  $x=[$b3];
  $y=[$k=>$x];

  return $y;
};
