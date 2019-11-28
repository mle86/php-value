<?php

namespace mle86\Value\Tests;

use mle86\Value\AbstractSerializableValue;
use mle86\Value\InvalidArgumentException;
use mle86\Value\Tests\Helpers\TestSWrapper6;
use mle86\Value\Value;
use PHPUnit\Framework\TestCase;

/**
 * Tests a simple AbstractSerializableValue implementation
 * and its default methods inherited from AbstractSerializableValue.
 */
class SerializableValueTest extends TestCase
{

    const VALID_INPUT    = "61234";
    const INVALID_INPUT  = "61234 ";
    const VALID_INPUT2   = "69999";
    const INVALID_INPUT2 = "79990";


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
        $this->assertFalse(@($tw == 0),
            "serializable wrapper considered zero as ==equal !");
    }

    /**
     * @depends testInstance
     */
    public function testPhpSerialize(AbstractSerializableValue $tw): string
    {
        $ser = serialize($tw);
        $this->assertNotEmpty($ser);
        return $ser;
    }

    /**
     * @depends testPhpSerialize
     * @depends testInstance
     */
    public function testPhpUnserialize(string $serialization, AbstractSerializableValue $orig)
    {
        /** @var AbstractSerializableValue $uns */
        $uns = unserialize($serialization);

        $this->assertSame(get_class($orig), get_class($uns),
            "unserialize(serialize(ASV)) returned instance of a different class!");

        $this->assertTrue($orig->equals($uns),
            "Unserialized object doesn't equal the original object anymore!");
        $this->assertTrue($uns->equals($orig),
            "Original object doesn't equal an unserialized object anymore!");
    }

    /**
     * @depends testPhpSerialize
     * @depends testPhpUnserialize
     */
    public function testSerializedValueValidity()
    {
        $validInput   = self::VALID_INPUT2;
        $invalidInput = self::INVALID_INPUT2;
        if (strlen($validInput) !== strlen($invalidInput)) {
            // We just want to replace the stored string without breaking the serialization format.
            // That only works if the replacement string is of the same length
            // because the serialization contains a length indicator.
            $this->markTestSkipped("Cannot test invalid serialization; strings are not of same length");
        }

        $validSerialization = serialize(new TestSWrapper6($validInput));

        $reValidInput = '/\b' . preg_quote($validInput, '/') . '\b/';
        $invalidSerialization = preg_replace($reValidInput, $invalidInput, $validSerialization, -1, $nReplaced);
        if ($nReplaced !== 1) {
            $this->markTestSkipped("Cannot test invalid serialization; manipulation of serialization string failed");
        }

        $this->expectException(InvalidArgumentException::class);  // !
        unserialize($invalidSerialization);
    }

}
