<?php
$ignore=[1  ,  2  ,  3]; //no

function f1($a  ,  $b  ,  $c) {} //no

$f2=function($a  ,  $b  ,  $c) use ($x  ,  $y  ,  $z) { //no, no
  f1($a,$y,4); //yes
};

$f2(1,2,3); //yes

class X
{
  public static $x;

  public function __construct($a, $b, $c) //no
  {
    self::$x='f1';
  }

  public function a($a  ,  $b  ,  $c) //no
  {
    self::$x(3,5,9); //yes
    return $f2($a,2,$c); //yes
  }
}

X::$x(2,4,6); //yes
$x=new X(1,2,4); //yes
$x->a(-1,2,[1  ,  2  ,  4]); //yes, no
$x->{implode(',',['a'  ,  ''  ,  ''])}(9,9,9); //yes, no, yes

for($ti=0  ,  $tj=0;$ti<0;$ti++) //no
  1;

f1(function($a  ,  $b){return $a+$b;},function($x  ,  $y){return new class{public $x  ,  $y  ,  $z;};},3); //yes, no, no, no

f1(); //yes
f1(2); //yes

//yes
f1(1,
   2,
   3);


f1(   1,   $a,  'asdf');
func2(NULL,NULL,2);

class Y extends Z
{
  public function run()
  {
    return [$this->add(     1,   2),
            $this->multiply(NULL,'7',1),
            $this->divide(  $a,  NULL)];
  }
}

f1(  1,-2.0,  3);
f1(123, 4.11,-5);
