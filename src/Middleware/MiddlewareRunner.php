<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Middleware;

use Funeralzone\ValueObjectGenerator\Definitions\Definition;

interface MiddlewareRunner
{
    public function run(
        MiddlewareExecutionStage $stage,
        Definition $definition,
        string $outputFolderPath,
        ?MiddlewareRunProfile $runProfile = null
    ): void;
}
