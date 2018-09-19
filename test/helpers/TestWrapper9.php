<?php

namespace mle86\Value\Tests;

use mle86\Value\AbstractValue;

/**
 * Accepts strings of length 5 with the first letter being a '9'.
 */
class TestWrapper9 extends AbstractValue
{

    public static function IsValid($test)
    {
        if ($test instanceof self) {
            return true;
        }
        if (is_string($test) && strlen($test) === 5 && $test[0] === "9") {
            return true;
        }
        return false;
    }

}
