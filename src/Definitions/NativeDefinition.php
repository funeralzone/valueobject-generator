<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions;

final class NativeDefinition
{
    private $definition;

    public function __construct(array $definition)
    {
        $this->definition = $definition;
    }

    public function getModel(): array
    {
        return $this->definition['model'] ?? [];
    }

    public function getRootNamespace(): string
    {
        return $this->definition['rootNamespace'] ?? '';
    }

    public function getNamespace(): string
    {
        return $this->definition['namespace'] ?? '';
    }

    public function toArray(): array
    {
        return $this->definition;
    }
}
