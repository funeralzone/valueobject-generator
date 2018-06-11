<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions;

interface DefinitionInputValidator
{
    public function validate(array $rawDefinition): bool;
    public function errors(): array;
}
