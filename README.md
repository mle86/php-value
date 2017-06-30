# php-value

This PHP library provides a simple base class for Immutable Value Objects.
Those are objects which wrap exactly one value,
cannot be changed in any way,
have no additional state,
and carry some validation logic in the constructor.

It is released under the [MIT License](http://opensource.org/licenses/MIT).


# Simple use case:

```php
class OddNumber
	extends \mle86\Value\AbstractValue
{

    // The base class requires this boolean test method:
    public static function IsValid ($input) {
        return (is_int($input) && ($input % 2) === 1);
    }

    // Nothing else is needed.
}

function my_function (OddNumber $odd_argument) {
    /* No further validation of $odd_argument is necessary in this function,
     * it's guaranteed to contain an odd number. */
    print "Got an odd number here: " . $odd_argument->value();
}

$odd1 = new OddNumber(61);       // works as expected, $odd1->value() will return 61
$odd2 = new OddNumber(40);       // throws an InvalidArgumentException
$odd3 = new OddNumber("string"); // throws an InvalidArgumentException
$odd4 = new OddNumber(null);     // throws an InvalidArgumentException
```


# Installation:

Via Composer:  `$ ./composer.phar require mle86/value`

Or insert this into your project's `composer.json` file:

```js
"require": {
    "mle86/value": "^1.0"
}
```


# Minimum PHP version:

* PHP 5.4 is needed for the `AbstractSerializableValue` class, as it uses the `JsonSerializable` interface.

* PHP 5.3 is sufficient for the rest (the `Value` interface and `AbstractValue` base class).


# Classes and interfaces:

1. [Value](#value) (interface)
1. [AbstractValue](#abstractvalue)  (abstract class)
1. [AbstractSerializableValue](#abstractserializablevalue)  (abstract class)
1. [InvalidArgumentException](#invalidargumentexception)  (exception)
1. [NotImplementedException](#notimplementedexception)  (exception)


## Value

This interface specifies that all Value classes should have
* a constructor which takes exactly one argument,
* a value() method without arguments.


## AbstractValue

This immutable class wraps a single value per instance.
The constructor enforces validity checks on the input value.
Therefore, every class instance's wrapped value can be considered valid.

The validity checks are located in the IsValid class method which all
subclasses must implement.  It is a class method to allow validity checks
of external values without wrapping them in an instance.


* <code>public function \_\_construct ($raw\_value)</code>

	The constructor uses the subclass' `IsValid` method to test its input argument.
	Valid values are stored in the new instance, invalid values cause an `InvalidArgumentException` to be thrown.
	Other instances of the same class are always considered valid (*re-wrapping*).

* <code>public static function IsValid ($test\_value)</code>

	Checks the validity of a raw value. If it returns true, a new object can be instantiated with the same value.
	Implement this in every subclass!
	In the base class implementation, it simply throws a `NotImplementedException`.

* <code>final public function value ()</code>

	Returns the object's wrapped initializer value.

* <code>final public function equals ($test\_value)</code>

	This method performs an equality check on other instances or raw values.
	Objects are considered equal if and only if they are instances of the same subclass and carry the same `value()`.
	All other values are considered equal if and only if they are identical (`===`) to the current objects's `value()`.

* <code>final public static function Wrap (&$value)</code>

	Replaces a value (by-reference) with instance wrapping that value.
	This means of course that the call will fail with an `InvalidArgumentException` if the input value fails the subclass' `IsValid` check.
	If the value already is an instance, it won't be replaced.

* <code>final public static function WrapOrNull (&$value)</code>

	Like `Wrap()`, but won't change `NULL` values.

* <code>final public static function WrapArray (array &$array)</code>

	Will replace all values in an array with instances.
	The array will only be altered (by-reference) if all its values are valid.
	Array keys will be preserved.

* <code>final public static function WrapOrNullArray (array &$array)</code>

	Will replace all non-`NULL` values in an array with instances.
	The array will only be changed (by-reference) if all its values are valid (or `NULL`).
	Array keys will be preserved.


## AbstractSerializableValue

This extension of `AbstractValue` provides easy serializability for the Value objects.
It implements the PHP 5.4 [JsonSerializable](https://php.net/manual/class.jsonserializable.php) interface.

* <code>public function \_\_toString ()</code>

	Returns the wrapped value --
	like `value()`, but with an explicit `(string)` typecast.
	This allows string concatenation of Value objects.

* <code>public function jsonSerialize ()</code>

	Returns the wrapped value --
	like `value()`.
	This enables [json_encode()](https://secure.php.net/json_encode) to encode the Value object.


## InvalidArgumentException

An empty extension of PHP's `InvalidArgumentException`.


## NotImplementedException

An empty extension of PHP's `ErrorException`.

