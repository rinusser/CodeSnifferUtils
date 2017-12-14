<?php
$f2=function($x  ,  $y  ,  $z) use ($a  ,  $b  ,  $c) {
  f1(1 , 2, [9  ,  8] ,4); //after,before,both
};

$f2(1 ,2,[4  ,  2]); //after

class X
{
  public static $x;

  public function __construct($a  ,  $b  ,  $c)
  {
    self::$x='f1';
  }

  public function a($a  ,  $b  ,  $c)
  {
    self::$x(3,5 , 9); //after, before
    return $f2($a ,2, $c); //after, before
  }
}

X::$x(2,4, 6); //before
$x=new X( 1,2,4); //before
$x->a(-1, 2 ,[1  ,  2  ,  4] ); //both, after
$x->{implode( ',',['a'  ,  ''  ,  ''] )}(9,9,9 ); //before, after, after

for($ti=0  ,  $tj=0;$ti<0;$ti++)
  1;

f1( function($a  ,  $b){return $a+$b;} , function($x  ,  $y){},3 ); //both, before, after

f1( ); //instead
f1( $x ); //both

//this should find before, after and around
f1( 1,
   2 , 
   3 );
