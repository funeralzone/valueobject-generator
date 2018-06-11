<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Repositories\ModelDecorators;

use Funeralzone\ValueObjectGenerator\Repositories\ExternalModels\Exceptions\ExternalModelTypeDoesNotExist;

final class NullModelDecoratorRepository implements ModelDecoratorRepository
{
    public function has(string $item): bool
    {
        return false;
    }

    public function get(string $item): ModelDecorator
    {
        throw new ExternalModelTypeDoesNotExist($item);
    }

    public function all(): array
    {
        return [];
    }
}
