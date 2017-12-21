<?php
$a= 1;        //after
$aa =1;       //before
$aaa = 1;     //around

$b  = 1;      //after
$bb =1;
$bbb=1;

$c   &=1;     //before
$cc  &=1;     //before
$ccc &=1;     //before

$x=[1 => 1,   //after
    11=> 2,   //after
    111 =>3]; //before

$y=[1  => 1,  //after
    11 =>2,
    111=>3];

$z=[1   => 1, //around
    11  =>2,  //before
    111 =>3]; //before

$d  **=1;     //before
$dd *=1;      //before
$ddd^=1;
