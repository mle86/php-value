<?php

namespace mle86\Value\Tests;

use mle86\Value\AbstractValue;
use mle86\Value\Tests\Helpers\TestWrapper4;
use mle86\Value\Value;
use PHPUnit\Framework\TestCase;

/**
 * Ensures that AbstractValue instances
 * cannot have any magic properties.
 *
 * Implementors might still define public properties
 * (although they're not supposed to),
 * but we cannot really prevent that.
 */
class MagicPropertiesTest extends TestCase
{

    const VALID_VALUE = "41111";

    /**
     * @return Value
     */
    public function testInstance()
    {
        $tw = new TestWrapper4(self::VALID_VALUE);

        $this->assertTrue(($tw && $tw instanceof TestWrapper4 && $tw instanceof AbstractValue && $tw instanceof Value));
        $this->assertSame(self::VALID_VALUE, $tw->value());

        return $tw;
    }

    /**
     * @depends testInstance
     */
    public function testSetMagicProperty(Value $o)
    {
        $prop  = "magic_property_3453465110";
        $setto = 86;

        $pv = (isset($o->{$prop})) ? $o->{$prop} : null;
        $this->assertNull($pv,
            "Newly-create object already has a magic property?!");

        $e = null;
        try {
            $o->{$prop} = $setto;
        } catch (\Throwable $e) {
            // Good!
            // But we still need to check that property...
        }

        $this->assertNotNull($e,
            "Setting a magic property did NOT result in an exception!");

        $pv = (isset($o->{$prop})) ? $o->{$prop} : null;
        $this->assertNotEquals($setto, $pv,
            "Setting a magic property WORKED, despite throwing an exception!");
        $this->assertNull($pv,
            "Setting a magic property resulted in an exception, but also set the property to some unexpected value!");
    }

}
