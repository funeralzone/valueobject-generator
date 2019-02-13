<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions\Models;

use Funeralzone\ValueObjectGenerator\Definitions\Models\Exceptions\ModelDoesNotExist;

final class ModelRegister
{
    private $modelsByName = [];

    public function add(Model $model): void
    {
        if ($model instanceof DefinedModel) {
            $this->modelsByName[$model->definitionName()] = $model;
        }
    }

    public function has(string $name): bool
    {
        return array_key_exists($name, $this->modelsByName);
    }

    public function get(string $name): Model
    {
        if ($this->has($name) === false) {
            throw new ModelDoesNotExist($name);
        }
        return $this->modelsByName[$name];
    }

    public function all(): array
    {
        return array_values($this->modelsByName);
    }

    public function allByName(): array
    {
        return $this->modelsByName;
    }
}
