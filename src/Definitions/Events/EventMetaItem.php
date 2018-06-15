<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions\Events;

use Funeralzone\ValueObjectGenerator\Definitions\Models\Model;

final class EventMetaItem
{
    private $model;
    private $propertyName;
    private $metaKey;
    private $required;

    public function __construct(
        Model $model,
        string $propertyName,
        string $metaKey,
        bool $required
    ) {
        $this->model = $model;
        $this->propertyName = $propertyName;
        $this->metaKey = $metaKey;
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

    public function metaKey(): string
    {
        return $this->metaKey;
    }

    public function required(): bool
    {
        return $this->required;
    }
}
