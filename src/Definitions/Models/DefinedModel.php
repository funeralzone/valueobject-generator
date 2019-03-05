<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions\Models;

use Funeralzone\ValueObjectGenerator\Definitions\Models\Decorators\ModelDecoratorSet;
use Funeralzone\ValueObjectGenerator\Repositories\ModelTypes\ModelType;
use Funeralzone\ValueObjectGenerator\Testing\ModelTestStipulations;

final class DefinedModel implements Model
{
    private $modelRegister;
    private $parent;
    private $type;
    private $namespace;
    private $definitionName;
    private $externalToDefinition;
    private $properties;
    private $decorators;
    private $implementsInterfaces;
    private $testStipulations;
    private $childModels;

    public function __construct(
        ModelRegister $modelRegister,
        ?Model $parent,
        ModelType $type,
        ModelNamespace $namespace,
        string $definitionName,
        bool $externalToDefinition,
        ModelProperties $properties,
        ModelDecoratorSet $decorators,
        ModelInterfaces $implementsInterfaces,
        ModelTestStipulations $testStipulations = null,
        ModelSet $childModels
    ) {
        $this->modelRegister = $modelRegister;
        $this->parent = $parent;
        $this->type = $type;
        $this->namespace = $namespace;
        $this->definitionName = $definitionName;
        $this->externalToDefinition = $externalToDefinition;
        $this->properties = $properties;
        $this->decorators = $decorators;
        $this->implementsInterfaces = $implementsInterfaces;
        $this->testStipulations = $testStipulations;
        $this->childModels = $childModels;
    }

    public function modelRegister(): ModelRegister
    {
        return $this->modelRegister;
    }

    public function parent(): ?Model
    {
        return $this->parent;
    }

    public function namespace(): ModelNamespace
    {
        return $this->namespace;
    }

    public function definitionName(): string
    {
        return $this->definitionName;
    }

    public function type(): ModelType
    {
        return $this->type;
    }

    public function children(): ModelSet
    {
        return $this->childModels;
    }

    public function externalToDefinition(): bool
    {
        return $this->externalToDefinition;
    }

    public function decorators(): ModelDecoratorSet
    {
        return $this->decorators;
    }

    public function properties(): ModelProperties
    {
        return $this->properties;
    }

    public function testStipulations(): ?ModelTestStipulations
    {
        return $this->testStipulations;
    }

    public function interfaces(): ModelInterfaces
    {
        return $this->implementsInterfaces;
    }
}
