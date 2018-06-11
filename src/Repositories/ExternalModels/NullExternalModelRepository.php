<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Repositories\ExternalModels;

use Funeralzone\ValueObjectGenerator\Definitions\Models\Model;
use Funeralzone\ValueObjectGenerator\Repositories\ExternalModels\Exceptions\ExternalModelTypeDoesNotExist;

final class NullExternalModelRepository implements ExternalModelRepository
{
    public function has(string $item): bool
    {
        return false;
    }

    public function get(string $item): Model
    {
        throw new ExternalModelTypeDoesNotExist($item);
    }

    public function all(): array
    {
        return [];
    }
}
