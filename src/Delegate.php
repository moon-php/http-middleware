<?php

declare(strict_types=1);

namespace Moon\HttpMiddleware;

use Moon\HttpMiddleware\Exception\InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Delegate implements RequestHandlerInterface
{
    /**
     * @var string[]|MiddlewareInterface[]
     */
    protected $middlewares;
    /**
     * @var callable
     */
    private $default;
    /**
     * @var ContainerInterface|null
     */
    private $container;

    public function __construct(array $middlewares, callable $default, ContainerInterface $container = null)
    {
        $this->middlewares = $middlewares;
        $this->default = $default;
        $this->container = $container;
    }

    /**
     * @throws \Moon\HttpMiddleware\Exception\InvalidArgumentException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $middleware = \array_shift($this->middlewares);

        // It there's no middleware use the default callable
        if (null === $middleware) {
            return \call_user_func($this->default, $request);
        }

        if ($middleware instanceof MiddlewareInterface) {
            return $middleware->process($request, clone $this);
        }

        if (!$this->container instanceof ContainerInterface || !$this->container->has($middleware)) {
            throw new InvalidArgumentException($middleware);
        }

        \array_unshift($this->middlewares, $this->container->get($middleware));

        return $this->handle($request);
    }
}
