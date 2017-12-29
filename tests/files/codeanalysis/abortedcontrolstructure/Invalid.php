<?php

if(true); //error


if(true) {} //ok
else; //error


if(true) {} //ok
elseif(true); //error


foreach($x as $y); //error


while(true); //error


for($i=0;$i<10;$i++); //error


switch($x); //error

do
{
  while(true); //error
} while(true); //ok
