<?php
try {} catch(Exception $e) {}
try {} catch(InvalidArgumentException |Exception $asdf) {}
try {} catch(\InvalidArgumentException| Exception $x) {}
try {} catch(\InvalidArgumentException| Exception | A\B\C|D   $x) {}
