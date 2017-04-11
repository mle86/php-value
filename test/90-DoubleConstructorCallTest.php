<?php
namespace mle86\Value\Tests;

use mle86\Value\AbstractValue;
use mle86\Value\Value;
require_once 'helpers/TestWrapper4.php';


/**
 * Ensures that the class constructor
 * cannot be used to change the stored value.
 */
class DoubleConstructorCallTest
	extends \PHPUnit_Framework_TestCase
{

	const VALID_VALUE1 = "41111";
	const VALID_VALUE2 = "42222";

	/**
	 * @return Value
	 */
	public function testInstance () {
		$tw = new TestWrapper4 (self::VALID_VALUE1);

		$this->assertTrue(($tw && $tw instanceof TestWrapper4 && $tw instanceof AbstractValue && $tw instanceof Value),
			"new TestWrapper4() did not result in a valid object");

		$this->assertSame(self::VALID_VALUE1, $tw->value());

		return $tw;
	}

	/**
	 * @depends testInstance
	 */
	public function testDoubleConstructorCall (Value $o) {
		try {
			$o->__construct(self::VALID_VALUE2);
		} catch (\Throwable $e) {
			// Okay, this produced an exception.
			// But we still need to check the value...
		}

		$this->assertNotEquals(self::VALID_VALUE2, $o->value(),
			"Stored value can be CHANGED with a double constructor call!");
		$this->assertNotNull($o->value(),
			"Stored value was RESET by a double constructor call!");
		$this->assertEquals(self::VALID_VALUE1, $o->value(),
			"Stored value was UNEXPECTEDLY ALTERED by a double constructor call!");
	}

}

