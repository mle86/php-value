<?php

namespace mle86\Value\Tests;

use mle86\Value\AbstractValue;
use mle86\Value\NotImplementedException;
use PHPUnit\Framework\TestCase;

/**
 * Has no isValid method!
 */
class BadTestWrapper extends AbstractValue
{

}


class IncompleteClassTest extends TestCase
{

    public function testConstructor()
    {
        $this->expectException(NotImplementedException::class);
        new BadTestWrapper("1");
    }

}
