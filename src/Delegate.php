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
     * @var string[]|MiddlewareInterface[]|mixed $middlewares
     */
    protected $middlewares;
    /**
     * @var callable
     */
    private $default;
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Delegate constructor.
     *
     * @param array $middlewares
     * @param callable $default
     * @param ContainerInterface|null $container
     */
    public function __construct(array $middlewares, callable $default, ContainerInterface $container = null)
    {
        $this->middlewares = $middlewares;
        $this->default = $default;
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     * @throws \Moon\HttpMiddleware\Exception\InvalidArgumentException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $middleware = array_shift($this->middlewares);

        // It there's no middleware use the default callable
        if ($middleware === null) {
            return call_user_func($this->default, $request);
        }

        if ($middleware instanceof MiddlewareInterface) {

            return $middleware->process($request, clone $this);
        }

        if (!$this->container instanceof ContainerInterface || !$this->container->has($middleware)) {
            throw new InvalidArgumentException(
                sprintf('The middleware is not a valid %s and is not passed in the Container', MiddlewareInterface::class),
                $middleware
            );
        }

        array_unshift($this->middlewares, $this->container->get($middleware));

        return $this->handle($request);
    }
}