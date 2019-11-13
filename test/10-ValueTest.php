<?php

namespace mle86\Value\Tests;

use mle86\Value\AbstractValue;
use mle86\Value\Tests\Helpers\ExtTestWrapper4;
use mle86\Value\Tests\Helpers\TestWrapper4;
use mle86\Value\Tests\Helpers\TestWrapper9;
use mle86\Value\Value;
use PHPUnit\Framework\Error\Error;
use PHPUnit\Framework\TestCase;

/**
 * Tests two simple AbstractValue implementations,
 * their interactions (hopefully none)
 * and their default methods inherited from AbstractValue.
 */
class ValueTest extends TestCase
{

    /** @var TestWrapper4 */
    protected static $tw1;
    /** @var TestWrapper4 */
    protected static $tw2;


    public static function validInputs(): array { return [
        ["41111"],
        ["42020"],
    ]; }

    public static function invalidInputs(): array { return [
        [811],
        ["z"],
        [array("41111")],
        [null],
    ]; }

    public static function validInputs9(): array { return [
        ["91111"],
        ["92020"],
        ["93330"],
    ]; }


    public function testClassExists()
    {
        $class = 'mle86\\Value\\AbstractValue';

        $this->assertTrue(class_exists($class),
            "Class {$class} not found!");
        $this->assertTrue(is_a(AbstractValue::class, Value::class, true),
            "Class ".AbstractValue::class." does not implement the ".Value::class." interface!");
    }

    /**
     * @dataProvider validInputs
     * @depends testClassExists
     */
    public function testConstructor($initializer)
    {
        $tw = new TestWrapper4($initializer);
        $this->assertTrue(($tw && $tw instanceof TestWrapper4 && $tw instanceof AbstractValue && $tw instanceof Value),
            "new TestWrapper4() did not result in a valid object");

        // keep two instances for other tests:
        if (!self::$tw1) {
            self::$tw1 = $tw;
        } elseif (!self::$tw2) {
            self::$tw2 = $tw;
        }
    }

    /**
     * @dataProvider validInputs9
     * @depends testConstructor
     */
    public function testConstructor9($initializer)
    {
        $tw = new TestWrapper9($initializer);
        $this->assertTrue(($tw && $tw instanceof TestWrapper9 && $tw instanceof AbstractValue && $tw instanceof Value),
            "new TestWrapper9() did not result in a valid object");
    }

    /**
     * Has to fail, the provided initializers are illegal for both value classes.
     *
     * @dataProvider invalidInputs
     * @depends testConstructor
     */
    public function testInvalidInitializer($initializer)
    {
        $this->expectException(\InvalidArgumentException::class);
        new TestWrapper4($initializer);
    }

    /**
     * Has to fail, one class must not use another classes isValid check.
     *
     * @dataProvider validInputs9
     * @depends testInvalidInitializer
     * @depends testConstructor
     * @depends testConstructor9
     */
    public function testCrossInvalidInitializer($initializer)
    {
        $this->expectException(\InvalidArgumentException::class);
        new TestWrapper4($initializer);
    }


    /**
     * @depends testConstructor
     */
    public function testValue(): TestWrapper4
    {
        $vi = self::validInputs();
        $initializer = $vi[0][0];

        $w = new TestWrapper4($initializer);

        $value = $w->value();
        $this->assertTrue(isset($value),
            "wrapper object holds no value!");
        $this->assertEquals($initializer, $value,
            "wrapper object holds wrong value!");
        $this->assertSame($initializer, $value,
            "wrapper object holds correct value but of wrong type!");

        return $w;
    }

    /**
     * @depends testValue
     */
    public function testEquals()
    {
        // tw1 was constructed from validInputs[0][0]
        $vi = self::validInputs();
        $original_initializer = $vi[0][0];

        $this->assertTrue(self::$tw1->equals(self::$tw1),
            "wrapper failed self-equality check!");
        $this->assertTrue(self::$tw1->equals(self::$tw1->value()),
            "wrapper failed equality check on own value()!");
        $this->assertTrue(self::$tw1->equals($original_initializer),
            "wrapper failed equality check on own initializer!");

        $this->assertFalse(self::$tw2->equals(self::$tw1),
            "wrapper returned false-positive on equality check with different object!");
        $this->assertFalse(self::$tw2->equals(self::$tw1->value()),
            "wrapper returned false-positive on equality check with different object's value()!");
        $this->assertFalse(self::$tw2->equals($original_initializer),
            "wrapper returned false-positive on equality check with different initializer!");
    }

    /**
     * @depends testEquals
     */
    public function testBuiltinEqualsZero()
    {
        $this->expectException(Error::class);
        $this->assertFalse((self::$tw1 == 0 || 0 == self::$tw1),
            "wrapper is considered equal to zero by builtin== !");
    }

    /**
     * @depends testEquals
     */
    public function testBuiltinEquals()
    {
        $this->assertFalse((self::$tw1 == self::$tw2),
            "wrapper is considered equal to different object by builtin== !");
        $this->assertFalse((self::$tw1 == []),
            "wrapper is considered equal to [] by builtin== !");
        $this->assertFalse((self::$tw1 == new \stdClass()),
            "wrapper is considered equal to stdClass() by builtin== !");

        $this->assertTrue((self::$tw1 == self::$tw1),
            "wrapper fails builtin== self-equality check!");
    }

    /**
     * @depends testConstructor
     */
    public function testConstructorWithInstance()
    {
        $twx1 = new TestWrapper4(self::$tw1);
        $this->assertTrue(($twx1 && ($twx1 instanceof TestWrapper4)),
            "constructor did not handle instance argument correctly (should return new object with same class and value)");
        $this->assertTrue(self::$tw1->equals($twx1),
            "wrapper object constructed from instance has different value!");
        $this->assertTrue($twx1->equals(self::$tw1),
            "wrapper object constructed from instance has different value, AND equals() check is not reflexive!");
        $this->assertTrue(TestWrapper4::isValid($twx1->value()),
            "Token object constructed from instance is not self-valid anymore!");
    }


    /**
     * @depends testConstructor9
     */
    public function testWrap(): TestWrapper9
    {
        $vi = self::validInputs9();
        $v = $vi[0][0];
        $orig_v = $v;

        $ret = TestWrapper9::wrap($v);
        /** @type TestWrapper9 $v */

        $this->assertTrue(($ret && $ret instanceof TestWrapper9),
            "wrap() did not return an instance!");
        $this->assertNotSame($orig_v, $v,
            "wrap() did not change its argument in-place!");
        $this->assertSame($ret, $v,
            "wrap() changed its argument in-place, but not to the new instance!");
        $this->assertSame($orig_v, $ret->value(),
            "wrap() produced an instance containing wrong value!");
        $this->assertTrue($ret->equals($orig_v),
            "wrap() produced an object with equals(initializer) failure!");

        return $ret;
    }

    /**
     * @dataProvider validInputs9
     * @depends testWrap
     */
    public function testWrapInvalid($initializer)
    {
        $v = $initializer;
        $this->expectException(\InvalidArgumentException::class);
        TestWrapper4::wrap($v);
    }

    /**
     * @dataProvider validInputs9
     * @depends testWrapInvalid
     */
    public function testWrapCrossInvalid($initializer)
    {
        $v = $initializer;
        $this->expectException(\InvalidArgumentException::class);
        TestWrapper4::wrap($v);
    }

    /**
     * @depends testWrap
     */
    public function testRewrap(TestWrapper9 $tw): TestWrapper9
    {
        $tx = $tw;
        TestWrapper9::wrap($tx);

        $this->assertTrue(($tx && ($tx instanceof TestWrapper9)),
            "re-wrapping an existing wrapper object returned something else!");
        $this->assertSame($tw->value(), $tx->value(),
            "re-wrapped object contains wrong value!");

        return $tx;
    }

    /**
     * Wrapping an instance of a different class must fail.
     *
     * @depends testRewrap
     */
    public function testRewrapInvalid(TestWrapper9 $tx)
    {
        $this->expectException(\InvalidArgumentException::class);
        TestWrapper4::wrap($tx);
    }


    /**
     * @depends testConstructor
     */
    public function testWrapArray()
    {
        $vi = self::validInputs();
        $a  = [
            'k1'   => $vi[0][0],
            'kk22' => $vi[1][0],
            0      => self::$tw1,  // includes an instance
        ];

        $orig_a = $a;
        $this->assertTrue((array_keys($a) === ['k1', 'kk22', 0]));

        $ret = TestWrapper4::wrapArray($a);
        /** @type TestWrapper4[] $a */

        $this->assertNotSame($a, $orig_a,
            "wrapArray() did not change its argument in-place!");
        $this->assertSame($ret, $a,
            "wrapArray() return value is different from its in-place changed argument!");

        $this->assertSame(count($orig_a), count($a),
            "wrapArray() changed the array size!");
        $this->assertSame(array_keys($a), ['k1', 'kk22', 0],
            "wrapArray() did not preserve array indices!");

        $this->assertTrue(
            ($a['k1'] instanceof TestWrapper4 &&
            $a['kk22'] instanceof TestWrapper4 &&
            $a[0] instanceof TestWrapper4),
            "wrapArray() did not wrap all array elements!");

        $this->assertTrue(
            ($a['k1']->equals($orig_a['k1']) &&
            $a['kk22']->equals($orig_a['kk22']) &&
            $a[0]->equals($orig_a[0])),
            "wrapArray() did not preserve index-value associations!");
    }

    /**
     * @depends testWrapArray
     */
    public function testWrapArray_empty()
    {
        $e   = [];
        $ret = TestWrapper9::wrapArray($e);

        $this->assertSame([], $e, "wrapArray() did not leave an empty array untouched!");
        $this->assertSame($e, $ret, "wrapArray([]) handled its argument correctly, but returned something else!");
    }

    /**
     * @dataProvider invalidInputs
     * @depends      testWrapArray
     */
    public function testWrapArray_invalids($invalid_initializer)
    {
        $vi = self::validInputs9();

        $a = [
            $vi[0][0],
            $invalid_initializer,
            'j' => $vi[2][0],
        ];

        $orig_a = $a;

        $ex = null;
        try {
            TestWrapper9::wrapArray($a);
        } catch (\InvalidArgumentException $ex) {
            // ok!
        }

        $this->assertTrue(($ex instanceof \Exception),
            "wrapArray() did not throw an Exception on invalid array elements!");
        $this->assertSame($orig_a, $a,
            "wrapArray() already partially altered its argument although it contained invalid elements!");
    }


    /**
     * This test assumes that wrap() works fine
     * and that wrapOptional() did not fundamentally alter it,
     * except for NULL treatment of course.
     *
     * @depends testWrap
     */
    public function testWrapOptional()
    {
        $vi   = self::validInputs();
        $v1   = $vi[0][0];
        $ret1 = TestWrapper4::wrapOptional($v1);

        $this->assertTrue(($ret1 && $ret1 instanceof TestWrapper4));
        $this->assertSame($ret1, $v1);

        $v0   = null;
        $ret0 = TestWrapper4::wrapOrNull($v0);  // legacy fn name

        $this->assertNull($ret0,
            "wrapOptional(NULL) did not return NULL!");
        $this->assertNull($v0,
            "wrapOptional(NULL) changed its argument!");
    }

    /**
     * @depends testWrapArray
     */
    public function testWrapOptionalsArray()
    {
        $vi = self::validInputs();
        $a  = [
            'k1' => $vi[0][0],
            'k2' => null,
            'k3' => $vi[1][0],
        ];
        $orig_a = $a;

        TestWrapper4::wrapOptionalsArray($a);

        $this->assertTrue((
            $a['k1'] instanceof TestWrapper4 &&
            $a['k3'] instanceof TestWrapper4),
            "wrapOptionalsArray() did not correctly wrap the non-NULL array contents!");
        $this->assertNull($a['k2'],
            "wrapOptionalsArray() did not preserve the input array's NULL element!");
        $this->assertTrue((
            $a['k1']->equals($orig_a['k1']) &&
            $a['k3']->equals($orig_a['k3'])),
            "wrapOptionalsArray() did not wrap the correct values!");

        $empty = [];
        TestWrapper4::wrapOrNullArray($empty);  // legacy fn name
        $this->assertSame([], $empty,
            "wrapOptionalsArray() did not handle empty array correctly!");
    }


    public static function additionalPropertyValues(): array { return [
        [0],
        [\PHP_INT_MAX],
        [false],
        [true],
        [null],
    ]; }

    /**
     * Tries to instantiate a wrapper object from an extended class, i.e. it has additional properties (for whatever reason).
     *
     * @depends testConstructor
     */
    public function testExtendedObject(): ExtTestWrapper4
    {
        $vi = self::validInputs();

        $ew = new ExtTestWrapper4($vi[1][0]);
        $this->assertTrue(($ew && $ew instanceof ExtTestWrapper4 && $ew instanceof AbstractValue && $ew instanceof Value),
            "new ExtTestWrapper4() did not result in a valid object!");

        return $ew;
    }

    /**
     * @dataProvider additionalPropertyValues
     * @depends testExtendedObject
     * @depends testBuiltinEquals
     */
    public function testExtendedBuiltinEquals($additional_property, ExtTestWrapper4 $ew)
    {
        $vi = self::validInputs();

        $ew0 = new ExtTestWrapper4($vi[1][0]);  // same initializer as $ew
        $ew2 = new ExtTestWrapper4($vi[0][0]);  // different

        $ew->set_additional_property($additional_property);
        $ew0->set_additional_property($additional_property);
        $ew2->set_additional_property($additional_property);

        $this->assertTrue(($ew == $ew),
            "An extended wrapper object failed the builtin== self-equality check!");
        $this->assertTrue(($ew0 == $ew && $ew == $ew0),
            "Two extended wrapper objects with same value() failed the builtin== equality check!");
        $this->assertTrue(($ew2 != $ew && $ew != $ew2),
            "Two extended wrapper objects with different value() falsely passed the builtin== equality check!");
    }

    /**
     * @dataProvider additionalPropertyValues
     * @depends testExtendedObject
     * @depends testExtendedBuiltinEquals
     * @depends testBuiltinEqualsZero
     */
    public function testExtendedBuiltinEqualsZero($additional_property, ExtTestWrapper4 $ew)
    {
        $ew->set_additional_property($additional_property);

        $this->expectException(Error::class);
        $this->assertFalse(($ew == 0 || 0 == $ew),
            "Extended wrapper object is considered equal to zero by builtin== !");
    }

}
