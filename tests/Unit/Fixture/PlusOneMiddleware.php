<?php

declare(strict_types=1);

namespace Moon\HttpMiddleware\Unit\Fixture;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class PlusOneMiddleware implements MiddlewareInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $total = $request->getAttribute('total');

        return $handler->handle($request->withAttribute('total', ++$total));
    }
}