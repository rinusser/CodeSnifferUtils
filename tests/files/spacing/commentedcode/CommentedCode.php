<?php //comment

namespace A\B;//fail here please
use X; //fail here please

//comment
use A; //succeed here please

class Y {} //comment
class B //fail here please
{
  public $a; //comment
  public $b; //succeed here please
  //comment
  public $c; //succeed here please

  public function asdf()
  {
  }//comment
  public $d; //fail here please
}
