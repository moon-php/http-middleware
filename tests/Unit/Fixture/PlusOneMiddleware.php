<?php

declare(strict_types=1);

namespace Moon\HttpMiddleware\Unit\Fixture;


use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PlusOneMiddleware implements MiddlewareInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): ResponseInterface
    {
        $total = $request->getAttribute('total');

        return $delegate->process($request->withAttribute('total', ++$total));
    }
}