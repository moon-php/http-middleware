<?php

declare(strict_types=1);

namespace Moon\HttpMiddleware\Unit;

use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Moon\HttpMiddleware\Delegate;
use Moon\HttpMiddleware\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class DelegateTest extends TestCase
{
    public function testEmptyArrayThrowInvalidArgumentException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Middlewares array can't be empty");
        new Delegate([]);
    }

    public function testInvlidArrayThrowInvalidArgumentException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('All the middlewares must implement ' . MiddlewareInterface::class);
        new Delegate(['invalid object']);
    }
}