<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Middleware;

use Funeralzone\ValueObjectGenerator\Definitions\Definition;
use Funeralzone\ValueObjectGenerator\Definitions\Models\Model;

interface Middleware
{
    public function getExecutionStage(): MiddlewareExecutionStage;
    public function run(Definition $definition, string $outputFolderPath, ?Model $model): void;
}
