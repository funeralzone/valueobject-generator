<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Middleware;

use Funeralzone\ValueObjectGenerator\Output\Middleware\Exceptions\InvalidMiddlewareWasSupplied;

final class MiddlewareSet
{
    private $middleware;

    public function __construct(array $middleware)
    {
        foreach ($middleware as $item) {
            if (! $item instanceof Middleware) {
                throw new InvalidMiddlewareWasSupplied;
            }
        }
        
        $this->middleware = $middleware;
    }

    public function all(): array
    {
        return $this->middleware;
    }
}
