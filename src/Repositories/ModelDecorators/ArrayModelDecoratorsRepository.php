<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Repositories\ModelDecorators;

use Funeralzone\ValueObjectGenerator\Repositories\ModelDecorators\Exceptions\InvalidModelDecoratorSupplied;
use Funeralzone\ValueObjectGenerator\Repositories\ModelDecorators\Exceptions\ModelDecoratorDoesNotExist;

final class ArrayModelDecoratorsRepository implements ModelDecoratorRepository
{
    private $decorators = [];

    public function __construct(array $decorators)
    {
        foreach ($decorators as $decorator) {
            if ($decorator instanceof ModelDecorator) {
                $this->decorators[$decorator->name()] = $decorator;
            } else {
                throw new InvalidModelDecoratorSupplied();
            }
        }
    }

    public function has(string $item): bool
    {
        return array_key_exists($item, $this->decorators);
    }

    public function get(string $item): ModelDecorator
    {
        if ($this->has($item)) {
            return $this->decorators[$item];
        } else {
            throw new ModelDecoratorDoesNotExist($item);
        }
    }

    public function all(): array
    {
        return $this->decorators;
    }
}
