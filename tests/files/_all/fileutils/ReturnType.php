<?php
function function_without() {}
$closure_without=function() {};

function function_regular(): int{}
$closure_regular=function(): StdClass {};

function function_nullable(): ?iterable {}
$closure_nullable=function(): ?object {};

function function_rootns(): \Exception {}
$closure_rootns=function(): \DateTime {};

function function_relns(): A\B\C {}
$closure_relns=function(): D\E\F {};

function function_absns(): \G\H\I {}
$closure_absns=function(): \J\K{};
