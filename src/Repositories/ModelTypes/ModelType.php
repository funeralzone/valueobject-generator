<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Repositories\ModelTypes;

use Funeralzone\ValueObjectGenerator\Definitions\Models\Model;
use Funeralzone\ValueObjectGenerator\Output\OutputTemplateRenderer;
use Funeralzone\ValueObjectGenerator\Output\OutputWriter;
use Funeralzone\ValueObjectGenerator\Repositories\Templates\TemplateRepository;
use Funeralzone\ValueObjectGenerator\Testing\ModelTestStipulations;

interface ModelType
{
    public function type(): string;

    public function templateRepository(): TemplateRepository;

    public function allowChildModels(): bool;

    public function schemaValidationRules(): array;

    public function testStipulations(Model $model): ModelTestStipulations;

    public function generate(
        OutputTemplateRenderer $outputTemplateRenderer,
        OutputWriter $outputWriter,
        Model $model
    ): void;
}
