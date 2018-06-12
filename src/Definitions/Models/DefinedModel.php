<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions\Models;

use Funeralzone\ValueObjectGenerator\Definitions\Location;
use Funeralzone\ValueObjectGenerator\Repositories\ModelDecorators\ModelDecorator;
use Funeralzone\ValueObjectGenerator\Repositories\ModelTypes\ModelType;

final class DefinedModel implements Model
{
    private $referenceLocation;
    private $instantiationLocation;
    private $definitionName;
    private $type;
    private $nullable;
    private $propertyName;
    private $external;
    private $export;
    private $childModels;
    private $properties;
    private $decorator;

    public function __construct(
        Location $referenceLocation,
        Location $instantiationLocation,
        string $definitionName,
        ModelType $type,
        bool $nullable,
        string $propertyName,
        bool $external,
        bool $export,
        ModelSet $childModels,
        ModelProperties $properties,
        ModelDecorator $decorator = null
    ) {
        $this->referenceLocation = $referenceLocation;
        $this->instantiationLocation = $instantiationLocation;
        $this->definitionName = $definitionName;
        $this->type = $type;
        $this->nullable = $nullable;
        $this->propertyName = $propertyName;
        $this->external = $external;
        $this->export = $export;
        $this->childModels = $childModels;
        $this->properties = $properties;
        $this->decorator = $decorator;
    }

    public function referenceLocation(): Location
    {
        return $this->referenceLocation;
    }

    public function instantiationLocation(): Location
    {
        return $this->instantiationLocation;
    }

    public function definitionName(): string
    {
        return $this->definitionName;
    }

    public function type(): ModelType
    {
        return $this->type;
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
        return $this->nullable;
    }

    public function external(): bool
    {
        return $this->external;
    }

    public function export(): bool
    {
        return $this->export;
    }

    public function children(): ModelSet
    {
        return $this->childModels;
    }

    public function creatable(): bool
    {
        return ! $this->external;
    }

    public function properties(): ModelProperties
    {
        return $this->properties;
    }

    public function decorator(): ?ModelDecorator
    {
        return $this->decorator;
    }
}
