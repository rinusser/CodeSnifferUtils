<?php

function add3missing1($a, $b, $c)
{
  $rv=$a+$b;
  return $rv;
}

function add3missing2($d, $e, $f)
{
  $rv=$d;
  return $rv;
}

/**
 * @param int $a used
 * @param int $b (unused)
 * @param int $c not used but missing tag
 * @param int $d used
 */
function add14ignore23($a, $b, $c, $d)
{
  $rv=$a+$d;
  return $rv;
}
