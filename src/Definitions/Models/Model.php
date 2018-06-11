<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions\Models;

use Funeralzone\ValueObjectGenerator\Definitions\Location;
use Funeralzone\ValueObjectGenerator\Repositories\ModelDecorators\ModelDecorator;
use Funeralzone\ValueObjectGenerator\Repositories\ModelTypes\ModelType;

interface Model
{
    public function referenceLocation(): Location;
    public function instantiationLocation(): Location;
    public function definitionName(): string;
    public function type(): ModelType;
    public function propertyName(): string;
    public function propertyNameUcFirst(): string;
    public function export(): bool;
    public function nullable(): bool;
    public function children(): ModelSet;
    public function creatable(): bool;
    public function properties(): ModelProperties;
    public function decorator(): ?ModelDecorator;
}
