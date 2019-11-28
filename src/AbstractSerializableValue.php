<?php

namespace mle86\Value;

/**
 * This extension of AbstractValue provides easy serializability
 * for the Value objects.  It implements the JsonSerializable interface.
 *
 * Standard PHP serialization via {@see serialize}/{@see unserialize} is always supported.
 * This class contains an extra {@see __wakeup} implementation
 * to make sure that unserialized instances always contain a valid value.
 *
 * @author Maximilian Eul
 * @link https://github.com/mle86/php-value
 */
abstract class AbstractSerializableValue extends AbstractValue implements \JsonSerializable
{

    /**
     * Returns the wrapped value like {@see value()}, but with an explicit
     * string typecast.  This allows string concatenation of Value objects.
     *
     * (The typecast is necessary to prevent type mismatch errors
     *  for number-wrapping classes.)
     */
    public function __toString(): string
    {
        return (string)$this->value();
    }

    /**
     * Returns the wrapped value -- like {@see value()}.
     * This allows {@see json_encode()} to encode the object.
     *
     * @return mixed
     */
    public function jsonSerialize()
    {
        return $this->value();
    }

    /**
     * Custom serialization for PHP7.4+.
     *
     * This method outputs our new serialization format:
     * just the stored value, nothing else.
     * Because it's only the one property we don't have to include the prop name either.
     * This makes the serialized form considerably shorter
     * and human-readable.
     *
     * Serializations in this form are accepted by the pre-PHP7.4 {@see __wakeup} method
     * introduced in v1.3.0/v2.2.0 of this library.
     * Older library versions won't be able to unserialize this format!
     *
     * @internal
     */
    public function __serialize(): array
    {
        /*
         * This method must have an array return type.
         * That array may be nested, associative, whatever.
         * But it's just one value -- so the simplest form is, of course, [0 => $value].
         *
         * Don't change this format!
         * If the serialization is passed back to an older version of this library,
         * it might not be recognizable anymore.
         * Support for this format was added in v2.2.0 (see __wakeup).
         */
        return [$this->value()];
    }

    /**
     * Unserialization helper method for PHP 7.4+.
     *
     * This methods reads our custom serialization format (see {@see __serialize})
     * but for backwards compatibility it also understands the older, PHP-internal format.
     * This is necessary to handle serializations that have been created with an older version of this library
     * or in a pre-PHP7.4 environment.
     *
     * @internal
     */
    public function __unserialize(array $serializedInput)
    {
        if (array_key_exists(0, $serializedInput)) {
            // This is the format returned by our __serialize implementation.
            $this->__construct($serializedInput[0]);
            return;
        }

        $legacyKey = "\0" . AbstractValue::class . "\0" . 'value';
        if (array_key_exists($legacyKey, $serializedInput)) {
            // This is the PHP-internal format returned by pre-7.4 serializing.
            $this->__construct($serializedInput[$legacyKey]);
            return;
        }

        throw new InvalidArgumentException('unknown ' . static::class . ' serialization format');
    }

    /**
     * This method ensures that unserialized instances are still valid.
     *
     * Serialization may be stored in databases for a long time;
     * if the class definition changes in between,
     * it's possible that a formerly-valid serialization
     * contains a value which is no longer considered valid.
     * That's why this method re-applies the {@see isValid} check.
     *
     * This method also provides forward compatibility to the new PHP 7.4
     * `__serialize`/`__unserialize` format we'll implement in v3.
     * It is only called in a PHP7.3 (or older) environment
     * as PHP 7.4+ ignores it if an __unserialize method is present.
     *
     * @todo This method can be removed when the library has a PHP 7.4+ requirement.
     * @internal
     */
    public function __wakeup()
    {
        if (isset($this->{'0'})) {
            /* We're running PHP 7.3 or older and this instance was just unserialized
             * from a serialization created with PHP 7.4+,
             * which just contains [0 => $value].
             * Luckily PHP 7.3's unserialize() can sort of handle that:
             * it will write the value into a magic property called '0'.
             * We'll just call the constructor (which hasn't happened yet)
             * to run validation and to set $isSet.  */
            $inputValue = $this->{'0'};
            unset($this->{'0'});
            $this->__construct($inputValue);
            return;
        }

        /*
         * We're running PHP 7.3 or older and this instance was just unserialized
         * from a serialization also created with PHP 7.3 or older.
         * In this case, the serialization contained both $value and $isSet
         * and there's nothing left to assign.
         * But we'll still run the validation
         * just in case the serialized value is no longer considered valid.
         */
        $storedValue = $this->value();
        if (!static::isValid($storedValue)) {
            $storedValue = (is_string($storedValue) || is_int($storedValue) || is_float($storedValue))
                ? "'{$storedValue}'"
                : gettype($storedValue);
            throw new InvalidArgumentException("not a valid serialized " . static::class . ": {$storedValue}");
        }
    }

}
