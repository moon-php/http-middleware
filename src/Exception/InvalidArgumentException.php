<?php

declare(strict_types=1);

namespace Moon\HttpMiddleware\Exception;

use Psr\Http\Server\MiddlewareInterface;
use Throwable;

class InvalidArgumentException extends \InvalidArgumentException
{
    public const ERROR_MESSAGE = 'The middleware is not a valid %s and is not passed in the Container. Given: %s';

    /**
     * @var mixed|null
     */
    private $invalidMiddleware;

    public function __construct($invalidMiddleware = null, $code = 0, Throwable $previous = null)
    {
        $message = \sprintf(self::ERROR_MESSAGE, MiddlewareInterface::class, $this->castStageToString($invalidMiddleware));
        parent::__construct($message, $code, $previous);
        $this->invalidMiddleware = $invalidMiddleware;
    }

    /**
     * Return the invalid middleware.
     *
     * @return mixed|null
     */
    public function getInvalidMiddleware()
    {
        return $this->invalidMiddleware;
    }

    private function castStageToString($stage): string
    {
        if (\is_scalar($stage)) {
            return (string) $stage;
        }

        if (\is_array($stage)) {
            return \json_encode($stage) ?: 'array';
        }

        if (\is_object($stage)) {
            return \get_class($stage);
        }

        return \json_encode($stage) ?: 'Closure';
    }
}
