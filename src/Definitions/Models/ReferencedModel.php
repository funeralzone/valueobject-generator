<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions\Models;

use Funeralzone\ValueObjectGenerator\Definitions\Location;
use Funeralzone\ValueObjectGenerator\Repositories\ModelTypes\ModelType;

final class ReferencedModel implements Model
{
    private $linkedModel;
    private $definitionName;
    private $properties;

    public function __construct(
        Model $linkedModel,
        string $definitionName,
        ModelProperties $properties
    ) {
        $this->linkedModel = $linkedModel;
        $this->definitionName = $definitionName;

        $this->properties = new ModelProperties(array_merge(
            $linkedModel->properties()->all(),
            $properties->all()
        ));
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

    public function external(): bool
    {
        return $this->linkedModel->external();
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
        return $this->properties;
    }

    public function nonNullDecorator(): ?ModelDecorator
    {
        return $this->linkedModel->nonNullDecorator();
    }

    public function nullDecorator(): ?ModelDecorator
    {
        return $this->linkedModel->nullDecorator();
    }

    public function nullableDecorator(): ?ModelDecorator
    {
        return $this->linkedModel->nullableDecorator();
    }
}
