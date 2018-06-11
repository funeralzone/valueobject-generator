<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions\Models;

use Funeralzone\ValueObjectGenerator\Definitions\Location;
use Funeralzone\ValueObjectGenerator\Repositories\ModelDecorators\ModelDecorator;
use Funeralzone\ValueObjectGenerator\Repositories\ModelTypes\ModelType;

final class LinkedModel implements Model
{
    private $linkedModel;
    private $definitionName;
    private $propertyName;

    public function __construct(
        Model $linkedModel,
        string $definitionName,
        string $propertyName
    ) {
        $this->linkedModel = $linkedModel;
        $this->definitionName = $definitionName;
        $this->propertyName = $propertyName;
    }

    public function referenceLocation(): Location
    {
        return $this->linkedModel->referenceLocation();
    }

    public function instantiationLocation(): Location
    {
        return $this->linkedModel->instantiationLocation();
    }

    public function definitionName(): string
    {
        return $this->definitionName;
    }

    public function type(): ModelType
    {
        return $this->linkedModel->type();
    }

    public function propertyName(): string
    {
        return $this->propertyName;
    }

    public function propertyNameUcFirst(): string
    {
        return ucfirst($this->propertyName);
    }

    public function nullable(): bool
    {
        return $this->linkedModel->nullable();
    }

    public function export(): bool
    {
        return false;
    }

    public function children(): ModelSet
    {
        return $this->linkedModel->children();
    }

    public function creatable(): bool
    {
        return false;
    }

    public function properties(): ModelProperties
    {
        return $this->linkedModel->properties();
    }

    public function decorator(): ?ModelDecorator
    {
        return $this->linkedModel->decorator();
    }
}
