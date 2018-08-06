<?php

declare(strict_types=1);

namespace Moon\HttpMiddleware\Unit\Fixture;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class StoppingMiddleware implements MiddlewareInterface
{
    /**
     * @var ResponseInterface
     */
    private $fakeResponse;

    /**
     * StoppingMiddleware constructor.
     *
     * @param ResponseInterface $fakeResponse
     */
    public function __construct(ResponseInterface $fakeResponse)
    {
        $this->fakeResponse = $fakeResponse;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $delegate): ResponseInterface
    {
        return $this->fakeResponse;
    }
}
