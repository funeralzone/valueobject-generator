<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions\Models;

final class ModelPayloadItem
{
    private $model;
    private $propertyName;

    public function __construct(
        Model $model,
        string $propertyName
    ) {
        $this->model = $model;
        $this->propertyName = $propertyName;
    }

    public function model(): Model
    {
        return $this->model;
    }

    public function propertyName(): string
    {
        return $this->propertyName;
    }
}
