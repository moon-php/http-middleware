<?php

declare(strict_types=1);

namespace Moon\HttpMiddleware\Unit;

use Moon\HttpMiddleware\Delegate;
use Moon\HttpMiddleware\Exception\InvalidArgumentException;
use Moon\HttpMiddleware\Unit\Fixture\PlusOneMiddleware;
use Moon\HttpMiddleware\Unit\Fixture\PlusTwoMiddleware;
use Moon\HttpMiddleware\Unit\Fixture\StoppingMiddleware;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class DelegateTest extends TestCase
{
    public function testDefaultCallbackIsCalledOnEmptyMiddlewareStack()
    {
        $requestMock = $this->prophesize(ServerRequestInterface::class)->reveal();
        $responseMock = $this->prophesize(ResponseInterface::class)->reveal();
        $delegate = new Delegate([], function () use ($responseMock) {
            return $responseMock;
        });

        $this->assertSame($delegate->handle($requestMock), $responseMock);
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
        $delegate->handle($firstRequestProphecy->reveal());
    }

    public function testMiddlewareStackIsTraversedUsingContainer()
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

        $containerProphecy = $this->prophesize(ContainerInterface::class);
        $containerProphecy->has('one')->shouldBeCalled(1)->willReturn(true);
        $containerProphecy->has('two')->shouldBeCalled(1)->willReturn(true);
        $containerProphecy->get('one')->shouldBeCalled(1)->willReturn(new PlusOneMiddleware());
        $containerProphecy->get('two')->shouldBeCalled(1)->willReturn(new PlusTwoMiddleware());
        $containerMock = $containerProphecy->reveal();

        $delegate = new Delegate(['one', 'two'], $assertion, $containerMock);
        $delegate->handle($firstRequestProphecy->reveal());
    }

    public function testMiddlewareStackStop()
    {
        $requestMock = $this->prophesize(ServerRequestInterface::class)->reveal();
        $responseMock = $this->prophesize(ResponseInterface::class)->reveal();

        $delegate = new Delegate([new StoppingMiddleware($responseMock)], function () {
        });

        $this->assertSame($responseMock, $delegate->handle($requestMock));
    }

    public function testInvalidMiddlewareisPassedToInvalidArgumentException()
    {
        $this->expectException(InvalidArgumentException::class);
        $invalidMiddleware = new \SplStack();
        $requestMock = $this->prophesize(ServerRequestInterface::class)->reveal();
        $containerProphecy = $this->prophesize(ContainerInterface::class);
        $containerProphecy->has('InvalidMiddleware')->shouldBeCalled(1)->willReturn(true);
        $containerProphecy->has(Argument::any())->shouldBeCalled(2)->willReturn(false);
        $containerProphecy->get('InvalidMiddleware')->shouldBeCalled(1)->willReturn($invalidMiddleware);
        $containerMock = $containerProphecy->reveal();

        $delegate = new Delegate(['InvalidMiddleware'], function () {
        }, $containerMock);

        try {
            $delegate->handle($requestMock);
        } catch (InvalidArgumentException $e) {
            $this->assertSame($e->getInvalidMiddleware(), $invalidMiddleware);
            throw $e;
        }
    }

    public function testLazyLoadingMiddlewareFromContainer()
    {
        $requestMock = $this->prophesize(ServerRequestInterface::class)->reveal();
        $responseMock = $this->prophesize(ResponseInterface::class)->reveal();
        $middlewareProphecy = $this->prophesize(MiddlewareInterface::class);
        $middlewareProphecy->process(
            Argument::type(ServerRequestInterface::class), Argument::type(RequestHandlerInterface::class)
        )->shouldBeCalled(1)->willReturn($responseMock);
        $middlewareMock = $middlewareProphecy->reveal();
        $containerProphecy = $this->prophesize(ContainerInterface::class);
        $containerProphecy->has('validMiddleware')->shouldBeCalled(1)->willReturn(true);
        $containerProphecy->get('validMiddleware')->shouldBeCalled(1)->willReturn($middlewareMock);
        $containerMock = $containerProphecy->reveal();

        $delegate = new Delegate(['validMiddleware'], function () {
        }, $containerMock);

        $delegate->handle($requestMock);
    }

    public function testInvalidContainerEntry()
    {
        $this->expectException(InvalidArgumentException::class);

        $requestMock = $this->prophesize(ServerRequestInterface::class)->reveal();
        $containerProphecy = $this->prophesize(ContainerInterface::class);
        $containerProphecy->has('InvalidMiddleware')->shouldBeCalled(1)->willReturn(false);
        $containerProphecy->get('InvalidMiddleware')->shouldNotBeCalled(1);
        $containerMock = $containerProphecy->reveal();

        $delegate = new Delegate(['InvalidMiddleware'], function () {
        }, $containerMock);

        $delegate->handle($requestMock);
    }

    public function testInvalidMiddlewareAndContainerNotPassed()
    {
        $this->expectException(InvalidArgumentException::class);

        $requestMock = $this->prophesize(ServerRequestInterface::class)->reveal();

        $delegate = new Delegate(['InvalidMiddleware'], function () {
        });

        $delegate->handle($requestMock);
    }
}
