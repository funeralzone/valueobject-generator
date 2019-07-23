<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions;

interface DefinitionLoader
{
    public function load(array $rootNamespace, array $sources, Definition $baseDefinition = null): Definition;
}
