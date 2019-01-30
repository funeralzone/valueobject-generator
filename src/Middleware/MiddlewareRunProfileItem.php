<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Middleware;

final class MiddlewareRunProfileItem
{
    private $middleware;
    private $execute;

    public function __construct(Middleware $middleware, bool $execute)
    {
        $this->middleware = $middleware;
        $this->execute = $execute;
    }

    public function getMiddleware(): Middleware
    {
        return $this->middleware;
    }

    public function getExecute(): bool
    {
        return $this->execute;
    }
}
