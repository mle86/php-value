<?php

namespace mle86\Value;

/**
 * This extension of AbstractValue provides easy serializability
 * for the Value objects.  It implements the JsonSerializable interface.
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
