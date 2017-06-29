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

    public function __construct(array $middlewares)
    {
        if (empty($middlewares)) {
            throw new InvalidArgumentException("Middlewares array can't be empty");
        }

        foreach ($middlewares as $middleware) {
            if (!$middleware instanceof MiddlewareInterface) {
                throw new InvalidArgumentException('All the middlewares must implement ' . MiddlewareInterface::class);
            }
        }

        $this->middlewares = $middlewares;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ServerRequestInterface $request): ResponseInterface
    {
        /** @var MiddlewareInterface $middleware */
        $middleware = array_shift($this->middlewares);

        $next = null;
        if (!empty($this->middlewares)) {
            $next = new self($this->middlewares);
        }

        return $middleware->process($request, $next);
    }
}