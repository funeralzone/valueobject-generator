<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Repositories\ExternalModels;

use Funeralzone\ValueObjectGenerator\Definitions\Models\Model;
use Funeralzone\ValueObjectGenerator\Repositories\ExternalModels\Exceptions\ExternalModelTypeDoesNotExist;
use Funeralzone\ValueObjectGenerator\Repositories\ExternalModels\Exceptions\InvalidExternalModelSupplied;

final class ArrayExternalModelRepository implements ExternalModelRepository
{
    private $models = [];

    public function __construct(array $models)
    {
        foreach ($models as $model) {
            if ($model instanceof Model) {
                $this->models[$model->definitionName()] = $model;
            } else {
                throw new InvalidExternalModelSupplied;
            }
        }
    }

    public function has(string $item): bool
    {
        return array_key_exists($item, $this->models);
    }

    public function get(string $item): Model
    {
        if ($this->has($item)) {
            return $this->models[$item];
        } else {
            throw new ExternalModelTypeDoesNotExist($item);
        }
    }

    public function all(): array
    {
        return $this->models;
    }
}
