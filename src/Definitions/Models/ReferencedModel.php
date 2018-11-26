<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions\Models;

use Funeralzone\ValueObjectGenerator\Definitions\Models\Decorators\ModelDecoratorSet;
use Funeralzone\ValueObjectGenerator\Repositories\ModelTypes\ModelType;
use Funeralzone\ValueObjectGenerator\Testing\ModelTestStipulations;

final class ReferencedModel implements Model
{
    private $linkedModel;
    private $definitionName;
    private $properties;

    public function __construct(
        Model $linkedModel,
        string $definitionName,
        ModelProperties $properties
    ) {
        $this->linkedModel = $linkedModel;
        $this->definitionName = $definitionName;

        $this->properties = new ModelProperties(array_merge(
            $linkedModel->properties()->all(),
            $properties->all()
        ));
    }

    public function linkedModel(): Model
    {
        return $this->linkedModel;
    }

    public function namespace(): ModelNamespace
    {
        return $this->linkedModel->namespace();
    }

    public function definitionName(): string
    {
        return $this->definitionName;
    }

    public function type(): ModelType
    {
        return $this->linkedModel->type();
    }

    public function externalToDefinition(): bool
    {
        return $this->linkedModel->externalToDefinition();
    }

    public function children(): ModelSet
    {
        return $this->linkedModel->children();
    }

    public function creatable(): bool
    {
        return false;
    }

    public function properties(): ModelProperties
    {
        return $this->properties;
    }

    public function decorators(): ModelDecoratorSet
    {
        return $this->linkedModel->decorators();
    }

    public function testStipulations(): ?ModelTestStipulations
    {
        return $this->linkedModel->testStipulations();
    }
}
