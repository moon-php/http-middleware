<?php

declare(strict_types=1);

namespace Moon\HttpMiddleware\Unit\Fixture;


use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class StoppingMiddleware implements MiddlewareInterface
{
    /**
     * @var
     */
    private $fakeResponse;

    public function __construct($fakeResponse)
    {
        $this->fakeResponse = $fakeResponse;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): ResponseInterface
    {
        return $this->fakeResponse;
    }
}