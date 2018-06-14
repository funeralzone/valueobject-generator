<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Repositories\ModelTypes;

use Funeralzone\ValueObjectGenerator\Repositories\ModelTypes\Exceptions\ModelTypeDoesNotExist;

final class NullModelTypeRepository implements ModelTypeRepository
{
    public function has(string $item): bool
    {
        return false;
    }

    public function get(string $item): ModelType
    {
        throw new ModelTypeDoesNotExist($item);
    }

    public function all(): array
    {
        return [];
    }
}
