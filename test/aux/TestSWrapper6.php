<?php
namespace mle86\Value\Tests;

use mle86\Value\AbstractSerializableValue;


/**
 * Accepts strings of length 5 with the first letter being a '6'.
 */
class TestSWrapper6  extends AbstractSerializableValue {

	public static function IsValid ($test) {
		if ($test instanceof self)
			return true;
		elseif (is_string($test) && strlen($test) === 5 && $test[0] === "6")
			return true;
		else
			return false;
	}

}

