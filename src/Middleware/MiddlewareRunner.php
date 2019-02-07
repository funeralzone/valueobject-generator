<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Middleware;

use Funeralzone\ValueObjectGenerator\Definitions\Definition;
use Funeralzone\ValueObjectGenerator\Definitions\Models\Model;

interface MiddlewareRunner
{
    public function run(
        MiddlewareExecutionStage $stage,
        Definition $definition,
        string $outputFolderPath,
        ?Model $model = null,
        ?MiddlewareRunProfile $runProfile = null
    ): void;
}
