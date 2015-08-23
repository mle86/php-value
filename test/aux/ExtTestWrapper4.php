<?php
namespace mle86\Value\Tests;

use mle86\Value\AbstractValue;


/**
 * Like TestWrapper4, but has additional properties.
 * Accepts strings of length 5 with the first letter being a '4'.
 */
class ExtTestWrapper4  extends TestWrapper4 {

	public $additional_property = 0;

	public function set_additional_property ($new_value) {
		$this->additional_property = $new_value;
	}

}

