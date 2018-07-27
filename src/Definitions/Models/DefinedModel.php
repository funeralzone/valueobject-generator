<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions\Models;

use Funeralzone\ValueObjectGenerator\Definitions\Location;
use Funeralzone\ValueObjectGenerator\Repositories\ModelTypes\ModelType;
use Funeralzone\ValueObjectGenerator\Testing\ModelTestStipulations;

final class DefinedModel implements Model
{
    private $referenceLocation;
    private $instantiationLocation;
    private $definitionName;
    private $type;
    private $external;
    private $childModels;
    private $properties;
    private $nonNullDecorator;
    private $nullDecorator;
    private $nullableDecorator;
    private $testStipulations;

    public function __construct(
        Location $referenceLocation,
        Location $instantiationLocation,
        string $definitionName,
        ModelType $type,
        bool $external,
        ModelSet $childModels,
        ModelProperties $properties,
        ModelDecorator $nonNullDecorator = null,
        ModelDecorator $nullDecorator = null,
        ModelDecorator $nullableDecorator = null,
        ModelTestStipulations $testStipulations = null
    ) {
        $this->referenceLocation = $referenceLocation;
        $this->instantiationLocation = $instantiationLocation;
        $this->definitionName = $definitionName;
        $this->type = $type;
        $this->external = $external;
        $this->childModels = $childModels;
        $this->properties = $properties;
        $this->nonNullDecorator = $nonNullDecorator;
        $this->nullDecorator = $nullDecorator;
        $this->nullableDecorator = $nullableDecorator;
        $this->testStipulations = $testStipulations;
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

    public function external(): bool
    {
        return $this->external;
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

    public function nonNullDecorator(): ?ModelDecorator
    {
        return $this->nonNullDecorator;
    }

    public function nullDecorator(): ?ModelDecorator
    {
        return $this->nullDecorator;
    }

    public function nullableDecorator(): ?ModelDecorator
    {
        return $this->nullableDecorator;
    }

    public function testStipulations(): ?ModelTestStipulations
    {
        return $this->testStipulations;
    }
}
