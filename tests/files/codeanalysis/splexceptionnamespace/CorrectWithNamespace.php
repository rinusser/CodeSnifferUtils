<?php
namespace A\B;

use \LogicException;
use \OverflowException;

$x=new \InvalidArgumentException();
$y=new LogicException();

try
{
  throw new \DomainException();
  throw new SomeCustomException();
}
catch(SomeCustomException $e)
{
}
catch(\Exception $e)
{
}

try
{
  throw new LogicException();
}
catch(LogicException | \RuntimeException | SomeCustomException $e)
{
}

throw $x;
throw $y;

$misdirect=function() use ($x) {}
