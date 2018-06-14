<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Repositories\ModelDecorators;

use Funeralzone\ValueObjectGenerator\Repositories\ModelDecorators\Exceptions\ModelDecoratorDoesNotExist;

final class NullModelDecoratorRepository implements ModelDecoratorRepository
{
    public function has(string $item): bool
    {
        return false;
    }

    public function get(string $item): ModelDecorator
    {
        throw new ModelDecoratorDoesNotExist($item);
    }

    public function all(): array
    {
        return [];
    }
}
