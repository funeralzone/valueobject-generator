<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Middleware;

use Funeralzone\ValueObjectGenerator\Definitions\Definition;

interface Middleware
{
    public function getExecutionStage(): MiddlewareExecutionStage;
    public function run(Definition $definition, string $outputFolderPath);
}
