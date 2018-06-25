<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions;

interface DefinitionConverter
{
    public function convert(
        array $rootNamespace,
        string $definitionInput,
        Definition $baseDefinition = null
    ): Definition;
}
