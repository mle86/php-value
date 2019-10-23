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
 * @author Maximilian Eul
 * @link https://github.com/mle86/php-value
 */
interface Value
{

    public function __construct($rawValue);

    public function value();

}
