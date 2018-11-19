<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator;

use Funeralzone\ValueObjectGenerator\Definitions\Definition;
use Funeralzone\ValueObjectGenerator\Middleware\DefaultMiddlewareRunner;
use Funeralzone\ValueObjectGenerator\Middleware\MiddlewareExecutionStage;
use Funeralzone\ValueObjectGenerator\Output\ModelGenerator;

final class VOGenerator
{
    private $modelGenerator;
    private $middlewareRunner;

    public function __construct(
        DefaultMiddlewareRunner $middlewareRunner,
        ModelGenerator $modelGenerator
    ) {
        $this->middlewareRunner = $middlewareRunner;
        $this->modelGenerator = $modelGenerator;
    }

    public function generate(Definition $definition, string $outputFolderPath)
    {
        $this->runPreGenerationMiddleware($definition, $outputFolderPath);
        $this->generateModel($definition, $outputFolderPath);
        $this->runPostGenerationMiddleware($definition, $outputFolderPath);
    }

    private function runPreGenerationMiddleware(Definition $definition, string $outputFolderPath): void
    {
        $this->middlewareRunner->run(
            MiddlewareExecutionStage::PRE_GENERATION(),
            $definition,
            $outputFolderPath
        );
    }

    private function generateModel(Definition $definition, string $outputFolderPath): void
    {
        foreach ($definition->models()->all() as $model) {
            $this->modelGenerator->generate($model, $outputFolderPath);
        }
    }

    private function runPostGenerationMiddleware(Definition $definition, string $outputFolderPath): void
    {
        $this->middlewareRunner->run(
            MiddlewareExecutionStage::POST_GENERATION(),
            $definition,
            $outputFolderPath
        );
    }
}
