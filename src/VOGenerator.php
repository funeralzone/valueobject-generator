<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator;

use Funeralzone\ValueObjectGenerator\Definitions\Definition;
use Funeralzone\ValueObjectGenerator\Definitions\Models\Model;
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
        Definition $definition,
        string $outputFolderPath,
        ?MiddlewareRunProfile $middlewareRunProfile = null
    ): void {
        $this->runPreGenerationMiddleware($definition, $outputFolderPath, $middlewareRunProfile);
        $this->generateModel($definition, $outputFolderPath, $middlewareRunProfile);
        $this->runPostGenerationMiddleware($definition, $outputFolderPath, $middlewareRunProfile);
    }

    private function runPreGenerationMiddleware(
        Definition $definition,
        string $outputFolderPath,
        ?MiddlewareRunProfile $middlewareRunProfile
    ): void {
        $this->middlewareRunner->run(
            MiddlewareExecutionStage::PRE_GENERATION(),
            $definition,
            $outputFolderPath,
            null,
            $middlewareRunProfile
        );
    }

    private function generateModel(
        Definition $definition,
        string $outputFolderPath,
        ?MiddlewareRunProfile $middlewareRunProfile
    ): void {
        $models = $definition->models()->all();
        $modelCount = count($models);
        foreach ($models as $index => $model) {
            /** @var Model $model */
            $this->progressReporter->generateModelsProgress($modelCount, $index + 1);

            $this->modelGenerator->generate($model, $outputFolderPath);

            $this->applyPostModelGenerationMiddleware(
                $definition,
                $outputFolderPath,
                $model,
                $middlewareRunProfile
            );
        }
    }

    private function applyPostModelGenerationMiddleware(
        Definition $definition,
        string $outputFolderPath,
        Model $model,
        ?MiddlewareRunProfile $middlewareRunProfile
    ): void {

        $this->middlewareRunner->run(
            MiddlewareExecutionStage::POST_MODEL_INSTANCE_GENERATION(),
            $definition,
            $outputFolderPath,
            $model,
            $middlewareRunProfile
        );

        foreach ($model->children()->all() as $childModel) {
            $this->applyPostModelGenerationMiddleware(
                $definition,
                $outputFolderPath,
                $childModel,
                $middlewareRunProfile
            );
        }
    }

    private function runPostGenerationMiddleware(
        Definition $definition,
        string $outputFolderPath,
        ?MiddlewareRunProfile $middlewareRunProfile
    ): void {
        $this->middlewareRunner->run(
            MiddlewareExecutionStage::POST_GENERATION(),
            $definition,
            $outputFolderPath,
            null,
            $middlewareRunProfile
        );
    }
}
