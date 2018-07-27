<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions\Models;

use Funeralzone\ValueObjectGenerator\Definitions\Location;
use Funeralzone\ValueObjectGenerator\Repositories\ModelTypes\ModelType;
use Funeralzone\ValueObjectGenerator\Testing\ModelTestStipulations;

interface Model
{
    public function referenceLocation(): Location;
    public function instantiationLocation(): Location;
    public function definitionName(): string;
    public function type(): ModelType;
    public function external(): bool;
    public function children(): ModelSet;
    public function creatable(): bool;
    public function properties(): ModelProperties;
    public function nonNullDecorator(): ?ModelDecorator;
    public function nullDecorator(): ?ModelDecorator;
    public function nullableDecorator(): ?ModelDecorator;
    public function testStipulations(): ?ModelTestStipulations;
}
