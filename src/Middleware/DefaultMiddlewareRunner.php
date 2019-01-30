<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Middleware;

use Funeralzone\ValueObjectGenerator\Definitions\Definition;

final class DefaultMiddlewareRunner
{
    private $middlewareSet;

    public function __construct(MiddlewareSet $middlewareSet)
    {
        $this->middlewareSet = $middlewareSet;
    }

    public function run(
        MiddlewareRunProfile $runProfile,
        MiddlewareExecutionStage $stage,
        Definition $definition,
        string $outputFolderPath
    ): void {
        foreach ($this->middlewareSet->all() as $middleware) {
            if ($runProfile->shouldExecute($middleware)) {
                /** @var Middleware $middleware */
                if ($middleware->getExecutionStage()->getValue() == $stage->getValue()) {
                    $middleware->run($definition, $outputFolderPath);
                }
            }
        }

        return;
    }
}
