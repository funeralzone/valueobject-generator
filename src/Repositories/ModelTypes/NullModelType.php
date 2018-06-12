<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Repositories\ModelTypes;

use Funeralzone\ValueObjectGenerator\Definitions\Models\Model;
use Funeralzone\ValueObjectGenerator\Output\OutputTemplateRenderer;
use Funeralzone\ValueObjectGenerator\Output\OutputWriter;
use Funeralzone\ValueObjectGenerator\Repositories\Templates\NullTemplateRepository;
use Funeralzone\ValueObjectGenerator\Repositories\Templates\TemplateRepository;
use Funeralzone\ValueObjectGenerator\Testing\ModelTestStipulations;

class NullModelType implements ModelType
{
    public function type(): string
    {
        return '';
    }

    public function templateRepository(): TemplateRepository
    {
        return new NullTemplateRepository();
    }

    public function allowChildModels(): bool
    {
        return false;
    }

    public function ownSchemaValidationRules(): array
    {
        return [];
    }

    public function childSchemaValidationRules(): array
    {
        return [];
    }

    public function testStipulations(Model $model): ModelTestStipulations
    {
        return new ModelTestStipulations(
            '',
            []
        );
    }

    public function generate(
        OutputTemplateRenderer $outputTemplateRenderer,
        OutputWriter $outputWriter,
        Model $model
    ): void {
    }
}
