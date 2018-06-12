<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions\Events;

use Funeralzone\ValueObjectGenerator\Definitions\Models\Model;

final class EventPayloadItem
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
