<?php
namespace A\B;

$x=new \InvalidArgumentException();
$y=new LogicException();

try
{
  throw new LogicException($y);
  throw new SomeCustomException();
}
catch(LogicException | \RuntimeException | DomainException $e)
{
}

throw $x;
throw $y;
