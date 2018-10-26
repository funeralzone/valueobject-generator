<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Repositories\ModelTypes;

use Funeralzone\ValueObjectGenerator\Definitions\Models\Model;
use Funeralzone\ValueObjectGenerator\Output\GeneratedClassPaths\GeneratedClassPaths;
use Funeralzone\ValueObjectGenerator\Output\OutputWriter;
use Funeralzone\ValueObjectGenerator\Testing\ModelTestStipulations;

interface ModelType
{
    public function type(): string;

    public function allowChildModels(): bool;

    public function ownSchemaValidationRules(): array;

    public function childSchemaValidationRules(): array;

    public function testStipulations(Model $model): ModelTestStipulations;

    public function generatedClassPaths(Model $model): GeneratedClassPaths;

    public function buildModel(Model $model): Model;

    public function generate(
        OutputWriter $outputWriter,
        Model $model
    ): void;
}
