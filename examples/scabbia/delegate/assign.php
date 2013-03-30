<?php

use Scabbia\Delegate;

$x = Delegate::assign();

$x(
    function () {
        echo 'hello';
    },
    10
);

$x(
    function () {
        echo 'world';
    },
    11
);

$x()->invoke();
