<?php
declare(strict_types=1);

namespace Funeralzone\ValueObjectGenerator\Definitions;

use Funeralzone\ValueObjectGenerator\Definitions\Exceptions\InvalidNativeDefinitionSupplied;

final class NativeDefinitions
{
    private $nativeDefinitions;

    public function __construct(array $nativeDefinitions)
    {
        foreach ($nativeDefinitions as $nativeDefinition) {
            if (!$nativeDefinition instanceof NativeDefinition) {
                throw new InvalidNativeDefinitionSupplied;
            }
        }

        $this->nativeDefinitions = $nativeDefinitions;
    }

    public function all(): array
    {
        return $this->nativeDefinitions;
    }
}
