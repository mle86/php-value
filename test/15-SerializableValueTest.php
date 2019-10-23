<?php

namespace mle86\Value\Tests;

use mle86\Value\AbstractSerializableValue;
use mle86\Value\Tests\Helpers\TestSWrapper6;
use mle86\Value\Value;
use PHPUnit\Framework\Error\Error;
use PHPUnit\Framework\TestCase;

/**
 * Tests a simple AbstractSerializableValue implementation
 * and its default methods inherited from AbstractSerializableValue.
 */
class SerializableValueTest extends TestCase
{

    const VALID_INPUT   = "61234";
    const INVALID_INPUT = "61234 ";
    const VALID_INPUT2  = "69999";


    public function testClassExists()
    {
        $class = 'mle86\\Value\\AbstractSerializableValue';

        $this->assertTrue(class_exists($class),
            "Class {$class} not found!");
        $this->assertTrue(is_a(AbstractSerializableValue::class, Value::class, true),
            "Class ".AbstractSerializableValue::class." does not implement the ".Value::class." interface!");
    }

    /**
     * @depends testClassExists
     */
    public function testInstance(): AbstractSerializableValue
    {
        $tw = new TestSWrapper6(self::VALID_INPUT);

        $this->assertTrue(($tw && $tw instanceof TestSWrapper6 && $tw instanceof AbstractSerializableValue && $tw instanceof Value),
            "new TestSWrapper6() did not result in a valid object");

        return $tw;
    }

    /**
     * @depends testInstance
     */
    public function testString(AbstractSerializableValue $tw)
    {
        $s        = "<{$tw}>";
        $expected = "<" . self::VALID_INPUT . ">";

        $this->assertSame($expected, $s,
            "serializable wrapper has a __toString() method, but returned wrong value!");
    }

    /**
     * @depends testInstance
     */
    public function testJson(AbstractSerializableValue $tw)
    {
        $j        = json_decode(json_encode([$tw]));
        $expected = [$tw->value()];

        $this->assertSame($expected, $j,
            "serializable wrapper has a jsonSerialize() method, but returned wrong value!");
    }

    /**
     * @depends testInstance
     * @depends testString
     */
    public function testBuiltinEquals(AbstractSerializableValue $tw)
    {
        $this->assertTrue(($tw == self::VALID_INPUT),
            "serializable wrapper failed builtin== equality check with own initializer!");
        $this->assertFalse(($tw == self::VALID_INPUT2),
            "serializable wrapper considered other valid initializer as ==equal !");
        $this->assertFalse(($tw == self::INVALID_INPUT),
            "serializable wrapper considered other, invalid initializer as ==equal !");
    }

    /**
     * @depends testInstance
     * @depends testBuiltinEquals
     */
    public function testBuiltinEqualsZero(AbstractSerializableValue $tw)
    {
        $this->expectException(Error::class);
        $this->assertFalse(($tw == 0),
            "serializable wrapper considered zero as ==equal !");
    }

}
