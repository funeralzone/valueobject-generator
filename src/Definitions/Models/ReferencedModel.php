<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions\Models;

use Funeralzone\ValueObjectGenerator\Definitions\Models\Decorators\ModelDecoratorSet;
use Funeralzone\ValueObjectGenerator\Repositories\ModelTypes\ModelType;
use Funeralzone\ValueObjectGenerator\Testing\ModelTestStipulations;

final class ReferencedModel implements Model
{
    private $modelRegister;
    private $parent;
    private $definitionName;
    private $properties;

    public function __construct(
        ModelRegister $modelRegister,
        ?Model $parent,
        string $definitionName,
        ModelProperties $properties
    ) {
        $this->modelRegister = $modelRegister;
        $this->parent = $parent;
        $this->definitionName = $definitionName;
        $this->properties = $properties;
    }

    public function modelRegister(): ModelRegister
    {
        return $this->modelRegister;
    }

    public function parent(): ?Model
    {
        return $this->parent();
    }

    public function linkedModel(): Model
    {
        return $this->modelRegister->get($this->definitionName);
    }

    public function namespace(): ModelNamespace
    {
        return $this->linkedModel()->namespace();
    }

    public function definitionName(): string
    {
        return $this->definitionName;
    }

    public function type(): ModelType
    {
        return $this->linkedModel()->type();
    }

    public function externalToDefinition(): bool
    {
        return $this->linkedModel()->externalToDefinition();
    }

    public function children(): ModelSet
    {
        return $this->linkedModel()->children();
    }

    public function creatable(): bool
    {
        return false;
    }

    public function properties(): ModelProperties
    {
        return new ModelProperties(array_merge(
            $this->linkedModel()->properties()->all(),
            $this->properties->all()
        ));
    }

    public function decorators(): ModelDecoratorSet
    {
        return $this->linkedModel()->decorators();
    }

    public function testStipulations(): ?ModelTestStipulations
    {
        return $this->linkedModel()->testStipulations();
    }
}
