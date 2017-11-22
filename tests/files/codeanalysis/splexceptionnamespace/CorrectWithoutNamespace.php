<?php
use \LogicException;

$x=new \InvalidArgumentException();
$y=new LogicException();
$z=new DomainException();

try
{
  throw new \DomainException();
  throw new DomainException();
  throw new SomeCustomException();
}
catch(DomainException $e)
{
}
catch(\Exception $e)
{
}

try
{
  throw new LogicException();
}
catch(RuntimeException | LogicException | SomeCustomException $e)
{
}

throw $x;
throw $y;
