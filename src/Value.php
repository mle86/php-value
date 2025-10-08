<?php

namespace mle86\Value;

/**
 * The interface of all value classes,
 * i.e. AbstractValue and AbstractSerializableValue.
 *
 * It just specifies that Value classes  should have a 'value' method
 * and a one-argument constructor,  although those classes have another
 * important requirement:  being immutable.  But we cannot encode that
 * in an interface.
 *
 * @template T
 *
 * @author Maximilian Eul
 * @link https://github.com/mle86/php-value
 */
interface Value
{

    /**
     * @param T|static|mixed $rawValue
     */
    public function __construct($rawValue);

    /**
     * @return T
     */
    public function value();

}
