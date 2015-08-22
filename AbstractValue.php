<?php
namespace mle86\Value;


/**
 * This immutable class wraps a single value per instance.
 * The constructor enforces validity checks on the input value.
 * Therefore, every class instance's wrapped value can be considered valid.
 *
 * The validity checks are located in the IsValid class method which all
 * subclasses must implement.  It is a class method to allow validity checks
 * of external values without wrapping them in an instance.
 *
 *  Example: A sub-class named 'Prime' might perform a primality test in its
 *  IsValid method. Thus all 'Prime' instances are guaranteed to contain
 *  only a prime number, and methods with a type-hinted Prime argument
 *  don't have to do their own is_int() + is_positive() + is_prime() checks.
 *
 * The wrapped value can never be changed after instantiation.  (This is
 * enforced through the $value property being private. Subclasses could
 * theoretically still work around that with Reflection magic.)
 * As a typical 'value class', it does not have any other state either.
 *
 * There is a public getter for the contained value:  the value() method.
 * Additional getters may be implemented in subclasses (e.g. for different
 * representations of the value).
 *
 * @author Maximilian Eul
 */
abstract class AbstractValue
{

	/**
	 * This is the one value which this class wraps.
	 * It must only be written to in the class constructor.
	 *
	 * @var mixed
	 */
	private $value;

	/**
	 * Returns the object's wrapped initializer value.
	 *
	 * @return mixed
	 */
	final public function value () {
		return $this->value;
	}


	/**
	 * The constructor uses the subclass' IsValid method to test its input
	 * argument.  Valid values are stored in the new instance,  invalid values
	 * cause an InvalidArgumentException to be thrown.
	 * Other instance of the same class are always considered valid (re-wrapping).
	 *
	 * Subclasses are free to override this constructor,
	 * although they should always accept their own instances as input
	 * and should always use their own IsValid method on any other input.
	 * Since subclasses cannot directly access the $value property,
	 * they should always call this superconstructor to do the assignment.
	 *
	 * @param mixed|static $raw_value
	 */
	public function __construct ($raw_value) {

		if ($raw_value instanceof static) {
			/* Re-wrapping an existing instance works,
			 * the contained value has already passed the IsValid check once.  */
			$this->value = $raw_value->value();

		} elseif (static::IsValid($raw_value)) {
			$this->value = $raw_value;

		} else {
			$input = (is_string($raw_value) || is_int($raw_value) || is_float($raw_value))
				? "'{$raw_value}'"
				: gettype($raw_value);

			throw new InvalidArgumentException ("not a valid " . get_called_class() . ": {$input}");
		}
	}


	/**
	 * This method performs an equality check on other instances or raw values.
	 * Objects are considered equal if and only if they are instances of the same
	 * subclass and carry the same value().  All other values are considered equal
	 * if and only if they are identical (===) to the current objects's value().
	 *
	 * @param mixed|static $test_value
	 * @return bool
	 */
	final public function equals ($test_value) {
		if ($test_value instanceof static) {
			// It's an instance of the same class. Compare the wrapped values:
			return ($this->value() === $test_value->value());
		} else {
			// It's a raw value. Compare it to this instance's value:
			return ($this->value() === $test_value);
		}
	}

}

