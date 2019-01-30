<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Middleware;

use Funeralzone\ValueObjectGenerator\Middleware\Exceptions\InvalidMiddlewareRunProfileItemWasSupplied;

final class MiddlewareRunProfile
{
    private $indexedItems = [];
    private $executeByDefault;

    public function __construct(array $items, bool $executeByDefault = false)
    {
        foreach ($items as $item) {
            if (! $item instanceof MiddlewareRunProfileItem) {
                throw new InvalidMiddlewareRunProfileItemWasSupplied;
            }

            $this->indexedItems[get_class($item->getMiddleware())] = $item;
        }

        $this->executeByDefault = $executeByDefault;
    }

    public function all(): array
    {
        return array_values($this->indexedItems);
    }

    public function shouldExecute(Middleware $middleware): bool
    {
        $middlewareClass = get_class($middleware);
        if (array_key_exists($middlewareClass, $this->indexedItems)) {
            /** @var MiddlewareRunProfileItem $item */
            $item = $this->indexedItems[$middlewareClass];
            return $item->getExecute();
        }

        return $this->executeByDefault;
    }
}
