<?php

namespace mle86\Value\Tests\Helpers;

use mle86\Value\AbstractValue;

/**
 * Accepts strings of length 5 with the first letter being a '4'.
 */
class TestWrapper4 extends AbstractValue
{

    public static function IsValid($test)
    {
        if ($test instanceof self) {
            return true;
        }
        if (is_string($test) && strlen($test) === 5 && $test[0] === "4") {
            return true;
        }
        return false;
    }

}
