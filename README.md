# php-value

[![Build Status](https://travis-ci.org/mle86/php-value.svg?branch=master)](https://travis-ci.org/mle86/php-value)
[![Coverage Status](https://coveralls.io/repos/github/mle86/php-value/badge.svg?branch=master)](https://coveralls.io/github/mle86/php-value?branch=master)
[![Latest Stable Version](https://poser.pugx.org/mle86/value/version)](https://packagist.org/packages/mle86/value)
[![PHP 7.0](https://img.shields.io/badge/php-7.0-8892BF.svg?style=flat)](https://php.net/)
[![License](https://poser.pugx.org/mle86/value/license)](https://packagist.org/packages/mle86/value)

This PHP library provides a simple base class for Immutable Value Objects.
Those are objects which wrap exactly one value,
cannot be changed in any way,
have no additional state,
and carry some validation logic in the constructor.

It is released under the [MIT License](http://opensource.org/licenses/MIT).


# Simple use case:

```php
class OddNumber extends \mle86\Value\AbstractValue
{

    // The base class requires this boolean test method:
    public static function isValid($input): bool
    {
        return (is_int($input) && ($input % 2) === 1);
    }

    // Nothing else is needed.
}

function myFunction(OddNumber $oddArgument)
{
    /* No further validation of $oddArgument is necessary in this function,
     * it's guaranteed to contain an odd number. */
    print "Got an odd number here: " . $oddArgument->value();
}

$odd1 = new OddNumber(61);       // works as expected, $odd1->value() will return 61
$odd2 = new OddNumber(40);       // throws an InvalidArgumentException
$odd3 = new OddNumber("string"); // throws an InvalidArgumentException
$odd4 = new OddNumber(null);     // throws an InvalidArgumentException

$odd5   = OddNumber::optional(33);   // works as expected, $odd5->value() will return 33
$nonodd = OddNumber::optional(null); // $nonodd is now null
$odd6   = OddNumber::optional(40);   // throws an InvalidArgumentException
```


# Installation:

Via Composer:  `composer require mle86/value`

Or insert this into your project's `composer.json` file:

```json
"require": {
    "mle86/value": "^2"
}
```


# Minimum PHP version:

PHP 7.0


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

The validity checks are located in the isValid class method which all
subclasses must implement.  It is a class method to allow validity checks
of external values without wrapping them in an instance.


* <code>public function <b>\_\_construct</b>($rawValue)</code>

  The constructor uses the `isValid` class method to test its input argument.
  Valid values are stored in the new instance, invalid values cause an `InvalidArgumentException` to be thrown.
  Other instances of the same class are always considered valid (*re-wrapping*).

* <code>public static function <b>optional</b>($rawValue): ?static</code>

  Same as the default constructor,
  but also accepts `null` values (which will be returned unchanged).

* <code>abstract public static function <b>isValid</b>($testValue): bool</code>

  Checks the validity of a raw value.
  If it returns true, a new object can be instantiated with that value.
  Implement this in every subclass!

* <code>final public function <b>value</b>(): mixed</code>

  Returns the object's wrapped initializer value.

* <code>final public function <b>equals</b>($testValue): bool</code>

  Equality test.
  This method performs an equality check on other instances or raw values.
  Objects are considered equal if and only if they are instances of the same subclass and carry the same `value()`.
  All other values are considered equal if and only if they are identical (`===`) to the current objects's `value()`.

* <code>final public static function <b>wrap</b>(&$value)</code>

  Replaces a value (by-reference) with instance wrapping that value.
  This means of course that the call will fail with an `InvalidArgumentException` if the input value fails the subclass' `isValid` check.
  If the value already is an instance, it won't be replaced.

* <code>final public static function <b>wrapOptional</b>(&$value)</code>

  Like `wrap()`, but won't change `null` values.

* <code>final public static function <b>wrapArray</b>(array &$array): array</code>

  Will replace all values in an array with instances.
  The array will only be altered (by-reference) if all its values are valid.
  Array keys will be preserved.

* <code>final public static function <b>wrapOptionalsArray</b>(array &$array): array</code>

  Will replace all non-`null` values in an array with instances.
  The array will only be changed (by-reference) if all its values are valid (or `null`).
  Array keys will be preserved.


## AbstractSerializableValue

This extension of `AbstractValue` provides easy serializability for the Value objects.
It implements the [JsonSerializable](https://php.net/manual/class.jsonserializable.php) interface.

* <code>public function <b>\_\_toString</b>(): string</code>

  Returns the wrapped value like `value()`, but with an explicit
  `string` typecast.  This allows string concatenation of Value objects.

* <code>public function <b>jsonSerialize</b>(): mixed</code>

  Returns the wrapped value –
  like `value()`.
  This enables [json\_encode()](https://secure.php.net/json_encode) to encode the object.


## InvalidArgumentException

An empty extension of PHP's `InvalidArgumentException`.
