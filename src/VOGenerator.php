<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator;

use Funeralzone\ValueObjectGenerator\Definitions\Definition;
use Funeralzone\ValueObjectGenerator\Middleware\DefaultMiddlewareRunner;
use Funeralzone\ValueObjectGenerator\Middleware\MiddlewareExecutionStage;
use Funeralzone\ValueObjectGenerator\Middleware\MiddlewareRunProfile;
use Funeralzone\ValueObjectGenerator\Output\ModelGenerator;
use Funeralzone\ValueObjectGenerator\Output\ProgressReporting\ProgressReporter;

final class VOGenerator
{
    private $modelGenerator;
    private $middlewareRunner;
    private $progressReporter;

    public function __construct(
        DefaultMiddlewareRunner $middlewareRunner,
        ModelGenerator $modelGenerator,
        ProgressReporter $progressReporter
    ) {
        $this->middlewareRunner = $middlewareRunner;
        $this->modelGenerator = $modelGenerator;
        $this->progressReporter = $progressReporter;
    }

    public function generate(
        MiddlewareRunProfile $middlewareRunProfile,
        Definition $definition,
        string $outputFolderPath
    ): void {
        $this->runPreGenerationMiddleware($middlewareRunProfile, $definition, $outputFolderPath);
        $this->generateModel($definition, $outputFolderPath);
        $this->runPostGenerationMiddleware($middlewareRunProfile, $definition, $outputFolderPath);
    }

    private function runPreGenerationMiddleware(
        MiddlewareRunProfile $middlewareRunProfile,
        Definition $definition,
        string $outputFolderPath
    ): void {
        $this->middlewareRunner->run(
            $middlewareRunProfile,
            MiddlewareExecutionStage::PRE_GENERATION(),
            $definition,
            $outputFolderPath
        );
    }

    private function generateModel(Definition $definition, string $outputFolderPath): void
    {
        $models = $definition->models()->all();
        $modelCount = count($models);
        foreach ($models as $index => $model) {
            $this->progressReporter->generateModelsProgress($modelCount, $index + 1);
            $this->modelGenerator->generate($model, $outputFolderPath);
        }
    }

    private function runPostGenerationMiddleware(
        MiddlewareRunProfile $middlewareRunProfile,
        Definition $definition,
        string $outputFolderPath
    ): void {
        $this->middlewareRunner->run(
            $middlewareRunProfile,
            MiddlewareExecutionStage::POST_GENERATION(),
            $definition,
            $outputFolderPath
        );
    }
}
