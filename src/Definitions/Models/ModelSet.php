<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions\Models;

use Countable;

final class ModelSet implements Countable
{
    private $modelRegister;

    private $models = [];

    public function __construct(ModelRegister $modelRegister)
    {
        $this->modelRegister = $modelRegister;
    }

    public function all(): array
    {
        return $this->models;
    }

    public function count()
    {
        return count($this->models);
    }

    public function add(Model $model): void
    {
        $this->models[] = $model;
        $this->modelRegister->add($model);
    }

    public function modelRegister(): ModelRegister
    {
        return $this->modelRegister;
    }
}
