<?php
$x=[$a , $b] ;

function f1($a ,  $b ,  $c)
{
  return function($x , $y) use ($k , $l) {
    return array_map($x , [$k , $y+$l])  ;
  } ;
}

class X
{
  const C=[2 , 4  ,  6]  ;

  private static $_x=[1 , 2  ,  3] ;

  public $a , $b ,
         $c ;

  protected function m1($a , $b)
  {
    for($x=1 ; $x<10  ;  $x++)
      $a++;
    return function($x , $y) use ($k , $l) {
      return array_map($x , [$k  ,  $y+$l]);
    };
  }
}
