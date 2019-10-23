<?php

namespace mle86\Value;

/**
 * This extension of AbstractValue provides easy serializability
 * for the Value objects.  It implements the PHP 5.4 JsonSerializable interface.
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
     *
     * @return string
     */
    public function __toString()
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

}
