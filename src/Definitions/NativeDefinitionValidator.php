<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions;

interface NativeDefinitionValidator
{
    public function validate(NativeDefinition $nativeDefinition): bool;
    public function errors(): array;
}
