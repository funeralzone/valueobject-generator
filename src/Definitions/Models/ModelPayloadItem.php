<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions\Models;

final class ModelPayloadItem
{
    private $model;
    private $propertyName;
    private $required;

    public function __construct(
        Model $model,
        string $propertyName,
        bool $required
    ) {
        $this->model = $model;
        $this->propertyName = $propertyName;
        $this->required = $required;
    }

    public function model(): Model
    {
        return $this->model;
    }

    public function propertyName(): string
    {
        return $this->propertyName;
    }

    public function required(): bool
    {
        return $this->required;
    }
}
