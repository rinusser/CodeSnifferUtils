<?php
function a() {} //testcase for CSU-65: functions/closures immediately after opening tag caused internal error

function add3($a, $b, $c)
{
  return $a+$b+$c;
}

/**
 * @param int $a used
 * @param int $b (unused)
 * @param int $c (unused)
 * @param int $d used
 */
function add14ignore23($a, $b, $c, $d)
{
  return $a+$d;
}
