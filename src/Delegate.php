<?php

declare(strict_types=1);

namespace Moon\HttpMiddleware;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Moon\HttpMiddleware\Exception\InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Delegate implements DelegateInterface
{
    /**
     * @var MiddlewareInterface[] $middlewares
     */
    protected $middlewares;
    /**
     * @var callable
     */
    private $default;

    /**
     * Delegate constructor.
     *
     * @param array $middlewares
     * @param callable $default
     *
     * @throws InvalidArgumentException
     */
    public function __construct(array $middlewares, callable $default)
    {
        foreach ($middlewares as $middleware) {
            if (!$middleware instanceof MiddlewareInterface) {
                throw new InvalidArgumentException('All the middlewares must implement ' . MiddlewareInterface::class);
            }
        }

        $this->middlewares = $middlewares;
        $this->default = $default;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ServerRequestInterface $request): ResponseInterface
    {
        /** @var MiddlewareInterface $middleware */
        $middleware = array_shift($this->middlewares);

        // It there's no middleware use the default callable
        if ($middleware === null) {
            return call_user_func($this->default, $request);
        }

        return $middleware->process($request, clone $this);
    }
}