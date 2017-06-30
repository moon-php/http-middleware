<?php

declare(strict_types=1);

namespace Moon\HttpMiddleware\Unit;

use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Moon\HttpMiddleware\Delegate;
use Moon\HttpMiddleware\Exception\InvalidArgumentException;
use Moon\HttpMiddleware\Unit\Fixture\PlusOneMiddleware;
use Moon\HttpMiddleware\Unit\Fixture\PlusTwoMiddleware;
use Moon\HttpMiddleware\Unit\Fixture\StoppingMiddleware;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class DelegateTest extends TestCase
{
    public function testInvlidArrayThrowInvalidArgumentException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('All the middlewares must implement ' . MiddlewareInterface::class);
        new Delegate(['invalid object'], function () {
        });
    }

    public function testDefaultCallbackIsCalledOnEmptyMiddlewareStack()
    {
        $requestMock = $this->prophesize(ServerRequestInterface::class)->reveal();
        $responseMock = $this->prophesize(ResponseInterface::class)->reveal();
        $delegate = new Delegate([], function () use ($responseMock) {
            return $responseMock;
        });

        $this->assertSame($delegate->process($requestMock), $responseMock);
    }

    public function testMiddlewareStackIsTraversed()
    {
        $firstRequestProphecy = $this->prophesize(ServerRequestInterface::class);
        $secondRequestProphecy = $this->prophesize(ServerRequestInterface::class);
        $thirdRequestMock = $this->prophesize(ServerRequestInterface::class)->reveal();

        $secondRequestProphecy->getAttribute('total')->shouldBeCalled(1)->willReturn(2);
        $secondRequestProphecy->withAttribute('total', 4)->shouldBeCalled(1)->willReturn($thirdRequestMock);

        $firstRequestProphecy->getAttribute('total')->shouldBeCalled(1)->willReturn(1);
        $firstRequestProphecy->withAttribute('total', 2)->shouldBeCalled(1)->willReturn($secondRequestProphecy->reveal());

        $responseMock = $this->createMock(ResponseInterface::class);
        $assertion = function (ServerRequestInterface $request) use ($thirdRequestMock, $responseMock) {
            $this->assertSame($thirdRequestMock, $request);
            return $responseMock;
        };

        $delegate = new Delegate([new PlusOneMiddleware(), new PlusTwoMiddleware()], $assertion);
        $delegate->process($firstRequestProphecy->reveal());
    }

    public function testMiddlewareStackStop()
    {
        $requestMock = $this->prophesize(ServerRequestInterface::class)->reveal();
        $responseMock = $this->prophesize(ResponseInterface::class)->reveal();

        $delegate = new Delegate([new StoppingMiddleware($responseMock)], function () {
        });

        $this->assertSame($responseMock, $delegate->process($requestMock));
    }
}