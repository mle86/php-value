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
 * Example: A sub-class named 'Prime' might perform a primality test in its
 *  IsValid method. Thus all 'Prime' instances are guaranteed to contain
 *  only a prime number, and methods with a type-hinted Prime argument
 *  don't have to do their own is_int() + is_positive() + is_prime() checks.
 *
 * The wrapped value can never be changed after instantiation.  (This is
 * enforced through the $value property being private. Subclasses could
 * theoretically still work around that with Reflection magic.)
 * As a typical 'value class', it does not have any other state either.
 *
 * There is a public getter for the contained value:  the {@see value()} method.
 * Additional getters may be implemented in subclasses (e.g. for different
 * representations of the value).
 *
 * @author Maximilian Eul
 * @link https://github.com/mle86/php-value
 */
abstract class AbstractValue implements Value
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
    final public function value()
    {
        return $this->value;
    }


    /**
     * This variable is used to prevent double constructor calls,
     * which could otherwise be used to change the wrapped value.
     *
     * @var bool
     */
    private $isSet = false;


    /**
     * The constructor uses the subclass' {@see IsValid} method to test its input
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
     * @param mixed|static $rawValue
     */
    public function __construct($rawValue)
    {

        if ($this->isSet) {
            throw new DoubleConstructorCallException("double constructor call is not allowed");
        }
        $this->isSet = true;

        if ($rawValue instanceof static) {
            /* Re-wrapping an existing instance works,
             * the contained value has already passed the IsValid check once.  */
            $this->value = $rawValue->value();

        } elseif (static::IsValid($rawValue)) {
            $this->value = $rawValue;

        } else {
            $input = (is_string($rawValue) || is_int($rawValue) || is_float($rawValue))
                ? "'{$rawValue}'"
                : gettype($rawValue);

            throw new InvalidArgumentException("not a valid " . get_called_class() . ": {$input}");
        }
    }

    /**
     * Same as the default constructor,
     * but also accepts `null` values (which will be returned unchanged).
     *
     * @param $rawValue
     * @return static|null
     */
    public static function optional($rawValue)
    {
        if ($rawValue === null) {
            return null;
        }

        return new static($rawValue);
    }


    /** @noinspection PhpDocMissingThrowsInspection */
    /**
     * Checks the validity of a raw value.  If this method returns true,
     * a new object can be instantiated with the same value.
     * Implement this in every subclass!
     *
     * This method stub is here because static methods cannot be declared abstract.
     * It ensures that instantiating a sub-class without this method will result
     * in an exception, not a PHP error about a missing method.
     *
     * Always include an 'if ($testValue instanceof static) { return true; }'
     * check, as already-wrapped values are always considered valid!
     *
     * @param mixed|static $testValue
     * @return bool
     */
    public static function IsValid(
        /** @noinspection PhpUnusedParameterInspection */
        $testValue
    ) {
        throw new NotImplementedException(get_called_class() . "::IsValid not implemented!");
    }


    /**
     * This method performs an equality check on other instances or raw values.
     * Objects are considered equal if and only if they are instances of the same
     * subclass and carry the same value().  All other values are considered equal
     * if and only if they are identical (===) to the current objects's value().
     *
     * @param mixed|static $testValue
     * @return bool
     */
    final public function equals($testValue)
    {
        if ($testValue instanceof static) {
            // It's an instance of the same class. Compare the wrapped values:
            return ($this->value() === $testValue->value());
        } else {
            // It's a raw value. Compare it to this instance's value:
            return ($this->value() === $testValue);
        }
    }


    /**
     * Replaces a value (by-reference) with an instance wrapping that value.
     * (It also returns the new wrapper object.)
     * This means of course that the call will fail with
     * an InvalidArgumentException if the input value fails the subclass'
     * IsValid check.  If the value already is an instance, it won't be replaced.
     *
     * @param mixed|static $value
     * @return static
     */
    final public static function Wrap(&$value)
    {
        if ($value instanceof static) {
            /* While re-wrapping would work, it's a waste of resources as it results in two identical objects.
             * Because the instances are immutable, we can just leave it as it is.  */
            return $value;
        }

        return ($value = new static($value));
    }

    /**
     * Like Wrap, but won't change `null` values.
     *
     * @param mixed|static|null $value
     * @return static|null
     */
    final public static function wrapOptional(&$value)
    {
        if ($value === null) {
            // ignore
            return $value;
        }

        return static::Wrap($value);
    }

    /**
     * Will replace all values in an array with instances.
     * The array will only be altered (by-reference) if all its values are valid.
     * (It also returns the altered array.)
     * Array keys will be preserved.
     *
     * @param mixed[]|static[] $array
     * @return static[]
     */
    final public static function WrapArray(array &$array)
    {
        $arrayCopy = $array;
        foreach ($arrayCopy as &$value) {
            if ($value instanceof static) {
                // See comment in Wrap() -- we don't have to re-wrap existing instances.
            } else {
                $value = new static ($value);
            }
        }

        // No exception so far? Ok, now save the array and return it:
        $array = $arrayCopy;
        return $array;
    }

    /**
     * Will replace all non-`null` values in an array with instances.
     * The array will only be changed (by-reference) if all its values are valid (or `null`).
     * (It also returns the altered array.)
     * Array keys will be preserved.
     *
     * @param mixed[]|static[]|null[] $array
     * @return static[]|null[]
     */
    final public static function wrapOptionalsArray(array &$array): array
    {
        $arrayCopy = $array;
        foreach ($arrayCopy as &$value) {
            if ($value instanceof static) {
                // See comment in Wrap() -- we don't have to re-wrap existing instances.
            } elseif ($value === null) {
                // ignore
            } else {
                $value = new static ($value);
            }
        }

        // No exception so far? Ok, now save the array and return it:
        $array = $arrayCopy;
        return $array;
    }

    /**
     * Immutable objects cannot have any magic properties,
     * as they would be public and therefore changeable.
     * This method prevents setting any magic methods.
     *
     * @throws NoMagicPropertiesException  (always)
     * @internal
     */
    final public function __set($name, $value)
    {
        throw new NoMagicPropertiesException("immutable objects cannot have magic properties");
    }


    // Legacy aliases:

    /**
     * @deprecated Use {@see wrapOptional} instead.
     * @param mixed|static|null $value
     * @return static|null
     */
    final public static function wrapOrNull(&$value)
    {
        return self::wrapOptional($value);
    }

    /**
     * @deprecated Use {@see wrapOptionalsArray} instead.
     * @param mixed[]|static[]|null[] $array
     * @return static[]|null[]
     */
    final public static function wrapOrNullArray(array &$array): array
    {
        return self::wrapOptionalsArray($array);
    }

}

