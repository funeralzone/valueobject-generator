<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions;

interface DefinitionsCombiner
{
    public function combine(
        NativeDefinitions $nativeDefinitions
    ): NativeDefinition;
}
