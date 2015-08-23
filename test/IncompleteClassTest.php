<?php
namespace mle86\Value\Tests;

use mle86\Value\AbstractValue;


/**
 * Has no IsValid method!
 */
class BadTestWrapper  extends AbstractValue { }


class IncompleteClassTest
	extends \PHPUnit_Framework_TestCase
{

	/**
	 * @expectedException \mle86\Value\NotImplementedException
	 */
	public function testConstructor () {
		new BadTestWrapper ("1");
	}

}

