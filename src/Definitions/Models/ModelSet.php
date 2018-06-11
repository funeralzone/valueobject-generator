<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions\Models;

use Countable;
use Funeralzone\ValueObjectGenerator\Definitions\Models\Exceptions\InvalidModel;
use Funeralzone\ValueObjectGenerator\Definitions\Models\Exceptions\ModelDoesNotExist;

final class ModelSet implements Countable
{
    private $models;

    public function __construct(array $models)
    {
        $this->validateInput($models);
        $this->models = $models;
    }

    public function all(): array
    {
        return $this->models;
    }

    public function hasPath(array $path): bool
    {
        $model = $this->findModel($this, $path);
        return $model !== null;
    }

    public function getByPath(array $path): Model
    {
        $model = $this->findModel($this, $path);
        if ($model) {
            return $model;
        } else {
            throw new ModelDoesNotExist($path);
        }
    }

    public function count()
    {
        return count($this->models);
    }

    private function findModel(ModelSet $models, array $path): ?Model
    {
        $currentPathElement = array_shift($path);
        $matchingModel = null;
        foreach ($models->all() as $model) {
            /** @var Model $model */
            if ($model->definitionName() == $currentPathElement) {
                if (count($path) === 0) {
                    $matchingModel = $model;
                    break;
                } else {
                    $matchingModel = $this->findModel($model->children(), $path);
                }
            }
        }

        return $matchingModel;
    }

    private function validateInput(array $models): void
    {
        foreach ($models as $model) {
            if (! $model instanceof Model) {
                throw new InvalidModel;
            }
        }
    }
}
