<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions;

interface DefinitionInputValidator
{
    public function validate(array $rawDefinition, Definition $baseDefinition = null): bool;
    public function errors(): array;
}
