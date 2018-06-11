<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions;

interface DefinitionConverter
{
    public function convert(string $definitionInput): Definition;
}
