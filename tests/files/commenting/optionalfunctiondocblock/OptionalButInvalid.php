<?php
declare(strict_types=1);

class A
{
  /**
   * @param int $x some parameter
   * @return int
   */
  private function valid(int $x): int
  {
    return $x+1;
  }

  /**
   * this is missing tags
   */
  protected function invalid(string $missing): void
  {
  }

  /**
   * valid
   * @return NULL
   */
  public function a()
  {
  }
}
