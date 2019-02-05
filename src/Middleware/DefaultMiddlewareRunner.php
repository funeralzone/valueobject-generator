<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Middleware;

use Funeralzone\ValueObjectGenerator\Definitions\Definition;
use Funeralzone\ValueObjectGenerator\Definitions\Models\Model;

final class DefaultMiddlewareRunner implements MiddlewareRunner
{
    private $middlewareSet;

    public function __construct(MiddlewareSet $middlewareSet)
    {
        $this->middlewareSet = $middlewareSet;
    }

    public function run(
        MiddlewareExecutionStage $stage,
        Definition $definition,
        string $outputFolderPath,
        ?Model $model = null,
        ?MiddlewareRunProfile $runProfile = null
    ): void {
        foreach ($this->middlewareSet->all() as $middleware) {
            if ($runProfile !== null && $runProfile->shouldExecute($middleware) == false) {
                continue;
            }

            /** @var Middleware $middleware */
            if ($middleware->getExecutionStage()->getValue() == $stage->getValue()) {
                $middleware->run($definition, $outputFolderPath, $model);
            }
        }

        return;
    }
}
